<?php

namespace App\Services\ScreenJSON\Model\Document;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\BookmarkInterface;
use App\Services\ScreenJSON\Interfaces\IdentificationInterface;

use App\Services\ScreenJSON\Exceptions\InvalidIDException;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use Ramsey\Uuid\Uuid;
use \Carbon\Carbon;

class Bookmark extends Assignable implements JsonSerializable, BookmarkInterface, IdentificationInterface
{
    public $required = ['element', 'description', 'scene', 'parent', 'title'];

    public $element;

    public $description;

    public $scene;

    public $parent;

    public $title;

    public $type;

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
