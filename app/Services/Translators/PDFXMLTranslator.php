<?php

namespace App\Services\Translators;

use App\Interfaces\TranslatorInterface;

use \Exception;

class PDFXMLTranslator extends Translator implements TranslatorInterface
{
  public function translate () : TranslatorInterface
  {
    if (! $this->content || ! is_object ($this->content) )
    {
      throw new Exception ("No content to translate.");
    }

    foreach ( $this->content->page AS $page )
    {

    }

    return $this;
  }
}
