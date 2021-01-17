<?php

namespace App\Services\ScreenJSON\Model\Document\Scene;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\IdentificationInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidIDException;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

use Ramsey\Uuid\Uuid;
use \Carbon\Carbon;

abstract class Element extends Assignable implements JsonSerializable, IdentificationInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['authors', 'charset', 'dir', 'id', 'parent', 'scene', 'type'];

    public $access = [];

    public $authors = [];

    public $charset = 'utf8';

    public $class;

    public $content;

    public $contributors = [];

    public $dir = 'ltr';

    public $dom;

    public $encryption;

    public $fov;

    public $id;

    public $interactivity = false;

    public $locked = false;

    public $omitted = false;

    public $parent;

    public $perspective = '2D';

    public $scene;

    public $styles = [];

    public $type = 'general';

    public $annotations = [];

    public $revisions = [];

    public function __assign ( ?string $id = null, ?array $assignable = null )
    {

        if ( !$id )
        {
            $this->id = Uuid::uuid4()->toString();
        }
        else
        {
            if ( !preg_match ('/^[\p{L}\p{N}_-]+$/u', $id) )
            {
                throw new InvalidIDException ("Identifiers may only contain letters, numbers, hyphens, and/or underscores.");
            }

            $this->id = $id;
        }

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

        $this->__defaults ();
    }

    private function __defaults ()
    {

    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }

}
