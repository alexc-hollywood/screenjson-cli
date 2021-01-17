<?php

namespace App\Services\ScreenJSON\Model\Security;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;

use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Encryption extends Assignable implements JsonSerializable, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['cipher', 'encoding'];

    public $cipher;

    public $encoding;

    public $hash;

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
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
