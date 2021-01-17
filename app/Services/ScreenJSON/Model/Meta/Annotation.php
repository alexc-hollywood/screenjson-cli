<?php

namespace App\Services\ScreenJSON\Model\Meta;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;

use App\Services\ScreenJSON\Interfaces\IdentificationInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;
use App\Services\ScreenJSON\Interfaces\AnnotationInterface;

use App\Services\ScreenJSON\Exceptions\InvalidIDException;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

use Ramsey\Uuid\Uuid;
use \Carbon\Carbon;

class Annotation extends Assignable implements JsonSerializable, IdentificationInterface, MetaObjectInterface, AnnotationInterface
{
    use AllowsMetaObject;

    public $required = ['id', 'content', 'contributor', 'created', 'parent'];

    public $color;

    public $content;

    public $contributor;

    public $created;

    public $highlight = [];

    public $id;

    public $parent;

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
        if ( !$this->created )
        {
            $this->created = Carbon::now()->format ('c');
        }
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);
        return $data;
    }
}
