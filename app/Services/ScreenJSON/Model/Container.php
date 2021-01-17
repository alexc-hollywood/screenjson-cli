<?php

namespace App\Services\ScreenJSON\Model;

use App\Services\ScreenJSON\Interfaces\IdentificationInterface;
use App\Services\ScreenJSON\Interfaces\ContainerInterface;

use App\Services\ScreenJSON\Model\Security\Encryption;
use App\Services\ScreenJSON\Model\Meta\Title;

use App\Services\ScreenJSON\Model\Rights\Author;
use App\Services\ScreenJSON\Model\Rights\License;

use App\Services\ScreenJSON\Model\Workflow\Revision;

use App\Services\ScreenJSON\Helpers\ColorFactory;

use \Carbon\Carbon;
use Illuminate\Support\Str;

class Container extends Assignable implements IdentificationInterface, ContainerInterface
{
    protected $required = ['charset', 'document', 'id', 'lang', 'locale', 'dir', 'title', 'guid'];

    public $charset = 'utf8';

    public $document;

    public $encryption;

    public $id = null;

    public $lang = 'en';

    public $license;

    public $locale = 'en-US';

    public $dir = 'ltr';

    public $taggable = [];

    public $title;

    public $guid = 'rfc4122';

    public $authors = [];

    public $colors = [];

    public $contributors = [];

    public $derivations = [];

    public $revisions = [];

    public $registrations = [];

    /**
     * Constructor.
     *
     * @param string $id
     * @param array $config
     */
    public function __construct ( ?string $id = null, ?array $assignable = null )
    {
        $this->__assign ($id, $assignable);
    }

    public function __defaults ()
    {
        if ( !$this->encryption )
        {
            $this->encryption = new Encryption ([
                'cipher' => 'aes-256-str',
                'hash' => 'sha256',
                'encoding' => 'hex',
            ]);
        }

        if ( !$this->title )
        {
            $this->title = new Title ([
                'en' => "Untitled Screenplay",
                'es' => "Guión sin título",
                'fr' => "Scénario sans titre",
            ]);
        }

        if ( !$this->license )
        {
            $this->license = new License ('CC-BY-NC-ND-2.5', 'https://spdx.org/licenses/CC-BY-NC-ND-2.5.html');
        }

        if ( !count($this->authors) )
        {
            array_push (
                $this->authors,
                new Author ((string) Str::uuid(), 'Unspecified', 'Author')
            );
        }

        if ( !count($this->revisions) )
        {
            array_push (
                $this->revisions,
                new Revision ((string) Str::uuid(), [
                    'parent'  => $this->id,
                    'index'   => 0,
                    'authors' => [$this->authors[0]->id],
                    'version' => 'draft',
                    'created' => Carbon::now ()->format ('c'),
                ])
            );
        }

        if ( !$this->colors )
        {
            $colors = (new ColorFactory)->create ();
            array_push ($this->colors, $colors['WHITE']); // page bg
            array_push ($this->colors, $colors['BLACK']); // type/text
        }
    }
}
