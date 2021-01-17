<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use \Exception;

use TitasGailius\Terminal\Terminal;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Services\Converters;

class Convert extends Command
{
    protected $mimes = [
      'application/pdf',
      'application/zip',
      'text/xml',
      'text/plain',
    ];

    protected $exts = [
      'pdf'       => Converters\PDFConverter::class,
      'fdx'       => Converters\FinalDraftConverter::class,
      'xml'       => Converters\OSFConverter::class,
      'celtx'     => Converters\CeltxConverter::class,
      'fountain'  => Converters\FountainConverter::class,
      'fadein'    => Converters\FadeInConverter::class,
    ];

    protected $signature = 'convert {input} {output?} {--lang=en} {--cover=1} {--v}';

    protected $description = 'Converts a screenplay file to ScreenJSON format.';

    protected $input_path;

    protected $mime_type;

    protected $ext;

    protected $output_path;

    protected $pdftohtml;

    private function human_filesize (int $bytes, int $decimals = 2) : string
    {
        $factor = floor((strlen($bytes) - 1) / 3);
        if ($factor > 0) $sz = 'KMGT';
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
    }

    public function system_check ()
    {
      if (! function_exists ('simplexml_load_string') )
      {
        $this->error ("PHP is missing XML extension.");
      }

      if (! function_exists ('zip_open') )
      {
        $this->error ("PHP is missing Zip extension.");
      }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $this->system_check ();

      $path = pathinfo ($this->argument('input'));

      $this->input_path = realpath ($this->argument('input'));

      if (! $this->input_path )
      {
        $this->error ("File (".$this->argument('input').") does not exist or can't be resolved by realpath().");
      }

      if ( $this->option ('v') )
      {
        $this->line ("Input: ".$this->input_path);
      }

      if ( $this->option ('v') )
      {
        $this->line ("Input exists: ".file_exists ($this->input_path));
      }

      if (! file_exists ($this->input_path) )
      {
        $this->error ("File (".$this->input_path.") does not exist.");
        die();
      }


      /*************************************************************************
      Check input mime type
      *************************************************************************/
      $this->mime_type = mime_content_type ($this->input_path);

      if ( $this->option ('v') )
      {
        $this->line ("Mime type: ".$this->mime_type);
      }

      if ( $this->option ('v') )
      {
        $this->line ("Acceptable mime type: ".in_array ($this->mime_type , $this->mimes));
      }

      if (! in_array ($this->mime_type , $this->mimes) )
      {
        $this->error ("File mime type (".$this->mime_type .") is not allowed.");
      }

      /*************************************************************************
      Check input extension
      *************************************************************************/
      $this->ext = pathinfo ($this->input_path, PATHINFO_EXTENSION);

      if ( $this->option ('v') )
      {
        $this->line ("Extension: ".$this->ext);
      }

      if ( $this->option ('v') )
      {
        $this->line ("Acceptable extension: ".in_array ($this->ext , array_keys ($this->exts)));
      }

      if (! in_array ($this->ext , array_keys ($this->exts)) )
      {
        $this->error ("File extension (".$this->ext .") is not allowed. The extension is used for selecting the input parser to use.");
      }

      /*************************************************************************
      Check input file existence, size, and dating
      *************************************************************************/

      if ( $this->option ('v') )
      {
        $this->line ("Input size: ".$this->human_filesize (filesize ($this->input_path)));
        $this->line ("Input modified: ".filemtime ($this->input_path));
      }

      /*************************************************************************
      Check input readability
      *************************************************************************/
      if ( $this->option ('v') )
      {
        $this->line ("Input readable: ".is_readable ($this->input_path));
      }

      if (! is_readable ($this->input_path) )
      {
        $this->error ("File (".$this->input_path .") is not readable.");
      }

      /*************************************************************************
      Check output param
      *************************************************************************/

      $this->output_path = base_path (pathinfo ($this->input_path, PATHINFO_FILENAME).'.json');

      // Note we can't use realpath() here, because it will return false when it finds a file doesn't exist.
      if ( $this->argument('output') )
      {
        $path = pathinfo ($this->argument('output'));

        // We cut out relative URLs here.They are too variable.
        if (! empty ($path['dirname']) )
        {
          if ($path['dirname'] == '.')
          {
            $this->output_path = base_path ($path['filename'].'.json');
          }
          else
          {
            if (! Str::startsWith ($path['dirname'], '/') )
            {
              $this->error ("Output path has to be an absolute filesystem url (/path/to/something.json) or the local directory (empty or ./).");
            }

            $this->output_path = $this->argument('output');
          }
        }
      }

      if ( $this->option ('v') )
      {
        $this->line ("Output: ".$this->output_path);
      }

      /*************************************************************************
      Check output writeability
      *************************************************************************/
      if ( $this->option ('v') )
      {
        $this->line ("Output writeable: ".is_writeable (pathinfo ($this->output_path, PATHINFO_DIRNAME)));
      }

      if (! is_writeable (pathinfo ($this->output_path, PATHINFO_DIRNAME)) )
      {
        $this->error ("Output directory (".pathinfo ($this->output_path, PATHINFO_DIRNAME) .") is not writeable.");
      }

      /*************************************************************************
      Check pdftotext is available if its a PDF
      *************************************************************************/
      if ( $this->mime_type == 'application/pdf' )
      {
        $this->pdftohtml = Terminal::run ('which pdftohtml;');

        if ( $this->option ('v') )
        {
          $this->line ("pdftohtml path: ".$this->pdftohtml->output ());
        }

        if ( empty ($this->pdftohtml->output ()) )
        {
          $this->error ("External binary pdftohtml is required for converting PDF files. Install apt-get install -y poppler-utils");
        }
      }

      /*************************************************************************
      Finally, run the conversion
      *************************************************************************/

      if (! isset ($this->exts[$this->ext]) )
      {
        $this->error ("Couldn't load converter.");
      }

      try
      {
        $this->conversion = app ($this->exts[$this->ext])
          ->load ($this->input_path)
          ->lang ($this->option ('lang', 'en'))
          ->cover ($this->option ('cover', 1))
          ->run ()
          ->save ($this->output_path);

        if (! file_exists ($this->output_path) )
        {
          $this->error ("Error saving ".$this->output_path);
        }

        if ( file_exists ($this->output_path) )
        {
          $this->info (filesize ($this->output_path));
        }
      }
      catch (Exception $e)
      {
        throw $e;
        $this->error ($e->getMessage());
      }

    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
