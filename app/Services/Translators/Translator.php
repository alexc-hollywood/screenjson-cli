<?php

namespace App\Services\Translators;

use App\Interfaces\TranslatorInterface;

use \SimpleXMLElement;

abstract class Translator
{
  protected $content;

  protected $lang;

  protected $screenjson;

  protected $uppercase_regex = '/\b([A-Z0-9\s]{2,}+)\b/';

  protected $meta;

  public function meta (array $meta) : TranslatorInterface
  {
    $this->meta = $meta;
    return $this;
  }

  public function content ($content, string $lang, int $cover) : TranslatorInterface
  {
    $this->content = $content;
    $this->lang = $lang;
    $this->cover = $cover;

    return $this;
  }

  public function result ()
  {
    return $this->screenjson;
  }

  public function siblings (SimpleXMLElement $root, string $name, string $tag)
  {
    $siblings = (array) $root->children();

    $non_empty = collect ($siblings[$name])->reject(function ($value, $key) use ($tag) {
        return empty ($value->{$tag});
    });

    return $non_empty;

  }

}
