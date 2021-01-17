<?php

namespace App\Services\Translators;

use App\Interfaces\TranslatorInterface;

use \Exception;

class OSFXMLTranslator extends Translator implements TranslatorInterface
{
  public function translate () : TranslatorInterface
  {
    if (! $this->content || ! is_object ($this->content) )
    {
      throw new Exception ("No content to translate.");
    }

    foreach ( $this->content->paragraphs->para AS $p )
    {

    }

    return $this;
  }
}
