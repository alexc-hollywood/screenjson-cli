<?php

namespace App\Services\ScreenJSON\Model;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\DocumentInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Model\Workflow\Status;

use App\Services\ScreenJSON\Model\Visualization\Style;

use App\Services\ScreenJSON\Model\Document\Bookmark;
use App\Services\ScreenJSON\Model\Document\Content;
use App\Services\ScreenJSON\Model\Document\Cover;
use App\Services\ScreenJSON\Model\Document\Header;
use App\Services\ScreenJSON\Model\Document\Footer;

use App\Services\ScreenJSON\Model\Meta\Title;

use App\Services\ScreenJSON\Helpers\StatusFactory;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

use Ramsey\Uuid\Uuid;
use \Carbon\Carbon;
use Illuminate\Support\Str;

class Document extends Assignable implements JsonSerializable, DocumentInterface
{
    public $required = ['cover', 'footer', 'header', 'status'];

    public $cover;

    public $header;

    public $footer;

    public $status;

    public $templates = [];

    public $bookmarks = [];

    public $scenes = [];

    public $styles = [];

    public function __construct ( ?array $assignable = null )
    {
        if ( $assignable && count ($assignable) )
        {
            foreach ($assignable AS $key => $value)
            {
                if ( !property_exists ($this, $key) )
                {
                    throw new InvalidParameterException ("Parameter {".$key."} is not assignable.");
                }

                $this->{$key} = $value;
            }
        }

        //$this->__defaults ();
    }

    private function __defaults ()
    {
        if (! $this->cover )
        {
            $this->cover = new Cover ([
                'title' => new Title ([
                    'en' => 'Add Your Screenplay Title Here',
                    'es' => "Agrega el título de tu guión aquí",
                    'fr' => "Ajoutez votre titre de scénario ici",
                ]),
                'authors' => [],
                'additional' => new Content ([
                    'en' => 'Add Any Additional Information Here',
                    'es' => "Agregue aquí cualquier información adicional",
                    'fr' => "Ajoutez des informations supplémentaires ici",
                ]),
                'derivations' => false,
            ]);
        }

        if (! $this->status )
        {
            $colors = (new StatusFactory)->create (1);
            $this->status = $colors['white'];
        }

        if (! count ($this->templates) )
        {
            array_push ($this->templates, 'default');
        }

        if (! count ($this->styles) )
        {
            array_push ($this->styles, new Style ('courier-12', 'font-family: courier; font-size: 12px;', true));
        }

        if (! $this->header )
        {
            $this->header = new Header ([
                'cover'     => false,
                'display'   => true,
                'start'     => 1,
                'omit'      => [0],
                'content'   => new Content ([
                    'en' => 'This is your header text.',
                    'es' => "Este es el texto de su encabezado.",
                    'fr' => "Ceci est votre texte d'en-tête.",
                ]),
            ]);
        }

        if (! $this->footer )
        {
            $this->footer = new Footer ([
                'cover'     => false,
                'display'   => true,
                'start'     => 1,
                'omit'      => [0],
                'content'   => new Content ([
                    'en' => 'This is your footer text.',
                    'es' => "Este es el texto de tu pie de página.",
                    'fr' => "Ceci est votre texte de pied de page.",
                ]),
            ]);
        }

        if (! count ($this->bookmarks) )
        {
            array_push ($this->bookmarks, new Bookmark ((string) Str::uuid(), [
                'parent' => null,
                'scene'  => 0,
                'type'   => 'action',
                'element'=> 0,
                'title'  => new Title ([
                    'en' => 'Example Bookmark One',
                    'es' => "Ejemplo de marcador uno",
                    'fr' => "Exemple de signet un",
                ]),
                'description' => new Content ([
                    'en' => 'Add descriptive information for your bookmark here',
                    'es' => "Agregue información descriptiva para su marcador aquí",
                    'fr' => "Ajoutez des informations descriptives pour votre favori ici"
                ])
            ]));
        }

    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);
        return $data;
    }
}
