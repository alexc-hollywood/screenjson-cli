<?php

namespace App\Services\ScreenJSON\Model\Document;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\CoverInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;
use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Cover extends Assignable implements JsonSerializable, CoverInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['authors', 'title'];

    public $authors = [];

    public $additional;

    public $derivations;

    public $templates = [];

    public $title;

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
        if (! count ($this->templates) )
        {
            array_push ($this->templates, 'default');
        }
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
