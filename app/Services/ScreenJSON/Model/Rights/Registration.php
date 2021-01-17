<?php

namespace App\Services\ScreenJSON\Model\Rights;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidRegistrationException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Registration extends Assignable implements JsonSerializable, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['created', 'authority', 'identifier', 'ref'];

    public $created;

    public $authority;

    public $identifier;

    public $modified;

    public $ref;

    public function __construct ( string $authority, string $identifier, string $created, string $ref )
    {
        if ( empty ($authority) )
        {
            throw new InvalidRegistrationException ("Copyright registration must specify an authority or holding entity (e.g. WGA).");
        }

        if ( empty ($identifier) )
        {
            throw new InvalidRegistrationException ("Copyright registration must specify an identifier.");
        }

        if ( empty ($created) || strtotime ($created) )
        {
            throw new InvalidRegistrationException ("Copyright registration must specify a valid date on which it was granted.");
        }

        if (! filter_var ($ref, FILTER_VALIDATE_URL) )
        {
            throw new InvalidLicenseException ("Registration must have a valid URL reference with full details.");
        }

        $this->authority = $authority;

        $this->identifier = $identifier;

        $this->created = $created;

        $this->ref = $ref;
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
