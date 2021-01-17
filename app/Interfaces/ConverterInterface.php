<?php

namespace App\Interfaces;

interface ConverterInterface
{
  public function load (string $input_file) : ConverterInterface;

  public function run () : ConverterInterface;

  public function save (string $output_file) : ConverterInterface;
}
