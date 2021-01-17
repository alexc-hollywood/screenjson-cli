<?php

namespace App\Services\Translators;

use App\Interfaces\TranslatorInterface;

use \Exception;

class CeltxRDFTranslator extends Translator implements TranslatorInterface
{
  public function translate () : TranslatorInterface
  {
    if (! $this->content || ! is_object ($this->content) )
    {
      throw new Exception ("No content to translate.");
    }

    foreach ( $this->content->getElementsByTagName('p') AS $p )
    {

    }

    return $this;
  }
}
