<?php

namespace App\Services\ScreenJSON\Model\Document;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\SceneInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidIDException;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use Ramsey\Uuid\Uuid;
use \Carbon\Carbon;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Scene extends Assignable implements JsonSerializable, SceneInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['id', 'body', 'heading', 'authors'];

    public $animals = [];

    public $body = [];

    public $cast = [];

    public $extra = [];

    public $heading;

    public $id;

    public $locations = [];

    public $moods = [];

    public $props = [];

    public $sfx = [];

    public $sounds = [];

    public $tags = [];

    public $vfx = [];

    public $wardrobe = [];

    public $authors = [];

    public $contibutors = [];

    public function __construct ( ?string $id = null, ?array $assignable = null )
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
