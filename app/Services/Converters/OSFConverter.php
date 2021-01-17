<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

use App\Services\Translators\OSFXMLTranslator;

class OSFConverter extends Converter implements ConverterInterface
{

  public function load (string $input_file) : ConverterInterface
  {
    $this->modified = filemtime ($input_file);

    $this->content = simplexml_load_file ($input_file);

    return $this;
  }

  public function run () : ConverterInterface
  {
    $this->translator = (new OSFXMLTranslator)->meta (['modified' => $this->modified])->content ($this->content, $this->lang, $this->cover)->translate ();

    $this->translation = $this->translator->result ();

    return $this;
  }

}
