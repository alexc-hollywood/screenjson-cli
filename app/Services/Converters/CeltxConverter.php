<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

use \DomDocument;

use App\Services\Translators\CeltxRDFTranslator;

class CeltxConverter extends Converter implements ConverterInterface
{

  private $project_rdf;

  private $script_html;

  public function load (string $input_file) : ConverterInterface
  {
    $this->modified = filemtime ($input_file);

    $zip = zip_open ($input_file);

    while ( $zip_entry = zip_read($zip) ) {

      if( zip_entry_name($zip_entry) == 'project.rdf' ) {
        if (zip_entry_open($zip, $zip_entry)) {
          $this->project_rdf = zip_entry_read($zip_entry, 1000000);
        }
      }

      if( stristr(zip_entry_name($zip_entry), 'script-') ) {
        if (zip_entry_open($zip, $zip_entry)) {
          $this->script_html = zip_entry_read($zip_entry, 1000000);
        }
      }

      zip_entry_close($zip_entry);
    } // end while

    $this->content = new DomDocument;

    $this->content->preserveWhiteSpace = false;
    $this->content->strictErrorChecking = false;

    libxml_use_internal_errors(true);

    if( !empty($this->script_html) )
    {
      $this->content->loadHtml($this->script_html);
      libxml_clear_errors();
    };

    return $this;
  }

  public function run () : ConverterInterface
  {
    $this->translator = (new CeltxRDFTranslator)->meta (['modified' => $this->modified])->content ($this->content, $this->lang, $this->cover)->translate ();

    $this->translation = $this->translator->result ();

    return $this;
  }

}
