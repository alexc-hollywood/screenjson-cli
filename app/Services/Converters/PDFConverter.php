<?php

namespace App\Services\Converters;

use App\Interfaces\ConverterInterface;

use TitasGailius\Terminal\Terminal;

use App\Services\Translators\PDFXMLTranslator;

use \Exception;

class PDFConverter extends Converter implements ConverterInterface
{
  private $pdf_xml;

  public function load (string $input_file) : ConverterInterface
  {
    $this->modified = filemtime ($input_file);

    $pdftohtml_path = Terminal::run ("which pdftohtml");

    if ( empty ($pdftohtml_path->output()) )
    {
      throw new Exception ("pdftohtml binary is not available.");
    }

    $this->pdf_xml = Terminal::run (trim($pdftohtml_path->output())." -xml -i -stdout ".$input_file);

    $this->content = simplexml_load_string ($this->pdf_xml->output());

    return $this;
  }

  public function run () : ConverterInterface
  {
    $this->translator = (new PDFXMLTranslator)->meta (['modified' => $this->modified])->content ($this->content, $this->lang, $this->cover)->translate ();

    $this->translation = $this->translator->result ();

    return $this;
  }

}
