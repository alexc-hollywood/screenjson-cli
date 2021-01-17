<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

abstract class Converter
{
  protected $modified;
  
  protected $content;

  protected $lang;

  protected $cover;

  public $json_error;

  protected $translator;

  public $translation;

  public function cover (int $start) : ConverterInterface
  {
    $this->cover = $start;

    return $this;
  }

  public function lang (string $lang) : ConverterInterface
  {
    $this->lang = $lang;

    return $this;
  }

  public function save (string $output_file) : ConverterInterface
  {
    if ( $this->translation )
    {
      file_put_contents (
        $output_file,
        json_encode ($this->translation, JSON_PRETTY_PRINT|JSON_FORCE_OBJECT|JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_AMP|JSON_UNESCAPED_UNICODE)
      );

      $this->json_error = json_last_error_msg ();
    }

    return $this;
  }

}
