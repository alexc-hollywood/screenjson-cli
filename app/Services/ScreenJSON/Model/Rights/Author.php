<?php

namespace App\Services\ScreenJSON\Model\Rights;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\IdentificationInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidIDException;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Author extends Assignable implements JsonSerializable, IdentificationInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['id', 'given', 'family'];

    public $id;

    public $given;

    public $family;

    public function __construct ( ?string $id = null, ?string $given = null, ?string $family = null )
    {
        if ( !$id )
        {
            $this->id = Uuid::uuid4()->toString();
        }
        else
        {
            $this->id = $id;
        }

        if (! $given )
        {
            throw new InvalidParameterException ("Author must have a given (first) name.");
        }

        if (! $family )
        {
            throw new InvalidParameterException ("Author must have a family (last) name.");
        }

        $this->given  = mb_convert_case ($given, MB_CASE_TITLE, 'UTF-8');
        $this->family = mb_convert_case ($family, MB_CASE_TITLE, 'UTF-8');
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
