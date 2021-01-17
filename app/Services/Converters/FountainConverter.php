<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

use App\Services\Translators\FountainTranslator;

use App\Services\Fountain\Parser;

class FountainConverter extends Converter implements ConverterInterface
{

  public function load (string $input_file) : ConverterInterface
  {
    $this->modified = filemtime ($input_file);

    $this->content = (new Parser)->parse_file ($input_file);

    return $this;
  }

  public function run () : ConverterInterface
  {
    $this->translator = (new FountainTranslator)->meta (['modified' => $this->modified])->content ($this->content, $this->lang, $this->cover)->translate ();

    $this->translation = $this->translator->result ();

    return $this;
  }

}
