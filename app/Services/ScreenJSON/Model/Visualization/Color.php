<?php

namespace App\Services\ScreenJSON\Model\Visualization;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;

use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

use OzdemirBurak\Iris\Color\Hex;

class Color extends Assignable implements JsonSerializable, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['rgb', 'title'];

    public $hex;

    public $rgb;

    public $title;

    public function __construct ( string $title, $config )
    {
        if ( empty ($title) )
        {
            throw new InvalidParameterException ("Color name cannot be blank.");
        }

        if ( !preg_match ('/^[\p{L}\p{N}_-]+$/u', $title) )
        {
            throw new InvalidParameterException ("Color names may only contain letters, numbers, hyphens, and/or underscores.");
        }

        if ( is_array ($config) )
        {
            if ( count ($config) != 3 )
            {
                throw new InvalidParameterException ("An RGB set must have 3 values.");
            }

            foreach ($config AS $val)
            {
                if ( !is_numeric ($val) || $val < 0 || $val > 255 )
                {
                    throw new InvalidParameterException ("Value of RGB configuration (".$val.") is invalid.");
                }
            }

            $this->rgb = $config;
        }

        if ( is_string ($config) )
        {
            /*
            if ( !preg_match ('^(\#[\da-f]{3}|\#[\da-f]{6}|rgba\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)(,\s*(0\.\d+|1))\)|hsla\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)(,\s*(0\.\d+|1))\)|rgb\(((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*,\s*){2}((\d{1,2}|1\d\d|2([0-4]\d|5[0-5]))\s*)|hsl\(\s*((\d{1,2}|[1-2]\d{2}|3([0-5]\d|60)))\s*,\s*((\d{1,2}|100)\s*%)\s*,\s*((\d{1,2}|100)\s*%)\))$', $config) )
            {
                throw new InvalidParameterException ("Color hex value is invalid.");
            }
            */

            $this->hex = $config;

            $this->rgb = (new Hex($config))->toRgb()->values();
        }

        $this->title = $title;
    }


    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required']);

        return $data;
    }
}
