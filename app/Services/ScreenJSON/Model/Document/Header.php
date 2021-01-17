<?php

namespace App\Services\ScreenJSON\Model\Document;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\HeaderInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Header extends Assignable implements JsonSerializable, HeaderInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = [];

    public $cover = false;

    public $content;

    public $display = true;

    public $omit = [];

    public $start = [];

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
