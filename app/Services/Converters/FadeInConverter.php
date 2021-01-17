<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

use App\Services\Translators\OSFXMLTranslator;

class FadeInConverter extends Converter implements ConverterInterface
{

  private $document_xml;

  public function load (string $input_file) : ConverterInterface
  {
    $this->modified = filemtime ($input_file);

    $zip = zip_open ($input_file);

    while ( $zip_entry = zip_read($zip) ) {

      if( zip_entry_name($zip_entry) == 'document.xml' ) {
        if (zip_entry_open($zip, $zip_entry)) {
          $this->document_xml = zip_entry_read($zip_entry, 1000000);
        }
      }
      zip_entry_close($zip_entry);
    }

    $this->content = simplexml_load_string ($this->document_xml);

    return $this;
  }

  public function run () : ConverterInterface
  {
    $this->translator = (new OSFXMLTranslator)->meta (['modified' => $this->modified])->content ($this->content, $this->lang, $this->cover)->translate ();

    $this->translation = $this->translator->result ();

    return $this;
  }

}
