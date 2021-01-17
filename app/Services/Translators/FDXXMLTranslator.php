<?php

namespace App\Services\Translators;

use App\Interfaces\TranslatorInterface;

use \Exception;
use Illuminate\Support\Str;

use App\Services\ScreenJSON\Helpers\ColorFactory;

use App\Services\ScreenJSON\Model\Container;
use App\Services\ScreenJSON\Model\Document AS DocumentWrapper;

use App\Services\ScreenJSON\Model\Security\Encryption;
use App\Services\ScreenJSON\Model\Meta\Title;
use App\Services\ScreenJSON\Model\Meta\Derivation;

use App\Services\ScreenJSON\Model\Rights\Author;
use App\Services\ScreenJSON\Model\Rights\License;
use App\Services\ScreenJSON\Model\Workflow\Status;
use App\Services\ScreenJSON\Model\Workflow\Revision;
use App\Services\ScreenJSON\Model\Visualization\Style;

use App\Services\ScreenJSON\Model\Document\Bookmark;
use App\Services\ScreenJSON\Model\Document\Content;
use App\Services\ScreenJSON\Model\Document\Cover;
use App\Services\ScreenJSON\Model\Document\Footer;
use App\Services\ScreenJSON\Model\Document\Header;
use App\Services\ScreenJSON\Model\Document\Scene AS SceneWrapper;

use \SimpleXMLElement;

class FDXXMLTranslator extends Translator implements TranslatorInterface
{
  public $container;

  private $statuses = ['white', 'blue', 'pink', 'yellow', 'green', 'goldenrod', 'buff', 'salmon', 'cherry'];

  private function container () : Container
  {
    $title_page_lines = $this->siblings ($this->content->TitlePage->Content, 'Paragraph', 'Text');

    /*************************************************************************
    Set up the basics
    *************************************************************************/
    $this->container  = new Container;
    $this->container->id      = (string) Str::uuid();
    $this->container->lang    = $this->lang;
    $this->container->charset = 'UTF-8';
    $this->container->dir     = 'ltr';

    $colors = collect((new ColorFactory)->create());
    array_push ($this->container->colors, $colors->get('WHITE'));
    array_push ($this->container->colors, $colors->get('BLACK'));

    /*************************************************************************
    Set the encryption
    *************************************************************************/
    $this->container->encryption = new Encryption ([
        'cipher'    => 'aes-256-cbc',
        'hash'      => 'sha256',
        'encoding'  => 'hex',
    ]);

    /*************************************************************************
    Set the copyright
    *************************************************************************/
    $this->container->license = new License ('CC-BY-NC-ND-2.5', 'https://spdx.org/licenses/CC-BY-NC-ND-2.5.html');

    /*************************************************************************
    Guess the document title
    *************************************************************************/
    $this->container->title = new Title ([
        $this->lang => (string) $title_page_lines->first()->Text,
    ]);

    /*************************************************************************
    Guess the authors
    *************************************************************************/
    array_push (
        $this->container->authors,
        new Author ((string) Str::uuid(), Str::beforeLast ((string) $title_page_lines->values()->get(2)->Text, " "), Str::afterLast ((string) $title_page_lines->values()->get(2)->Text, " "))
    );

    /*************************************************************************
    Guess the 'based on'
    *************************************************************************/
    foreach ($title_page_lines->values() AS $line)
    {
      if (Str::contains ($line->Text, 'Based'))
      {
        array_push (
            $this->container->derivations,
            new Derivation ((string) Str::uuid(), [
              'type' => 'unknown',
              'title' => new Title ([
                $this->lang => (string) $line->Text,
              ])
            ])
        );
      }
    }

    return $this->container;
  }

  private function document () : DocumentWrapper
  {
    $this->container->document = new DocumentWrapper();

    /*************************************************************************
    Set the file timestamps
    *************************************************************************/
    $this->container->document->meta = ['created' => date ('c', $this->meta['modified']), 'modified' => date('c', $this->meta['modified'])];

    /*************************************************************************
    Import bookmarks
    *************************************************************************/

    // Final draft sin't great with bookmarks.
    array_push ($this->container->document->bookmarks, new Bookmark ((string) Str::uuid(), [
        'parent' => null,
        'scene'  => 0,
        'type'   => 'action',
        'element'=> 0,
        'title'  => new Title ([
            $this->lang => 'Document start',
        ]),
        'description' => new Content ([
            $this->lang => 'Beginning of the document (first page)',
        ])
    ]));

    /*************************************************************************
    Guess the cover
    *************************************************************************/
    $title_page_lines = $this->siblings ($this->content->TitlePage->Content, 'Paragraph', 'Text');

    $this->container->document->cover = new Cover ([
        'title' => new Title ([
            $this->lang => $title_page_lines->values()->slice(0,3)->map(function ($item, $key) {
                return $item->Text->__toString();
            })->implode (' ')
        ]),
        'authors' => $this->container->authors[0]->id,
        'additional' => new Content ([
            $this->lang => $title_page_lines->values()->map(function ($item, $key) {
                return $item->Text->__toString();
            })->implode (" | ")
        ]),
        'derivations' => count ($this->container->derivations) > 0,
    ]);

    /*************************************************************************
    Guess the footer
    *************************************************************************/
    $this->container->document->footer = new Footer ([
        'cover'     => false,
        'display'   => true,
        'start'     => 1,
        'omit'      => [0],
        'content'   => new Content ([
            $this->lang => '',
        ]),
    ]);

    if ( isset ($this->content->HeaderAndFooter->Footer->Paragraph->Text) && !empty ((string)$this->content->HeaderAndFooter->Footer->Paragraph->Text) )
    {
      $this->container->document->footer = new Footer ([
          'cover'     => $this->content->HeaderAndFooter['FooterFirstPage'] == 'Yes' ? true : false,
          'display'   => $this->content->HeaderAndFooter['FooterVisible'] == 'Yes' ? true : false,
          'start'     => (int) $this->content->HeaderAndFooter['StartingPage'] ?? 1,
          'omit'      => [0],
          'content'   => new Content ([
              $this->lang => (string)$this->content->HeaderAndFooter->Footer->Paragraph->Text,
          ]),
      ]);
    }

    /*************************************************************************
    Guess the header
    *************************************************************************/
    $this->container->document->header = new Header ([
        'cover'     => false,
        'display'   => true,
        'start'     => 1,
        'omit'      => [0],
        'content'   => new Content ([
            $this->lang => '',
        ]),
    ]);

    if ( isset ($this->content->HeaderAndFooter->Header->Paragraph->Text) && !empty ((string)$this->content->HeaderAndFooter->Header->Paragraph->Text) )
    {
      $this->container->document->header = new Header ([
          'cover'     => $this->content->HeaderAndFooter['HeaderFirstPage'] == 'Yes' ? true : false,
          'display'   => $this->content->HeaderAndFooter['HeaderVisible'] == 'Yes' ? true : false,
          'start'     => (int) $this->content->HeaderAndFooter['StartingPage'] ?? 1,
          'omit'      => [0],
          'content'   => new Content ([
              $this->lang => (string)$this->content->HeaderAndFooter->Header->Paragraph->Text,
          ]),
      ]);
    }

    /*************************************************************************
    Guess the revision status
    *************************************************************************/

    $this->container->document->status = new Status ($this->statuses[(int)$this->content->Revisions['ActiveSet'] ?? 0], 1, date ('c', $this->meta['modified']));

    /*************************************************************************
    Set some styles and templates
    *************************************************************************/

    array_push ($this->container->document->templates, 'default');

    array_push ($this->container->document->styles, new Style ('courier-12', 'font-family: courier; font-size: 12px;', true));

    return $this->container->document;
  }

  private function scenes () : array
  {

  }

  public function translate () : TranslatorInterface
  {
    if (! $this->content || ! is_object ($this->content) )
    {
      throw new Exception ("No content to translate.");
    }

    $this->container();

    $this->document();

    dd(json_encode($this->container, JSON_PRETTY_PRINT));

    $this->scenes();





    $this->translation = $this->container;


    return $this;
  }
}
