<?php

namespace App\Services\ScreenJSON\Model\Document\Scene;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\SceneHeadingInterface;
use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Heading extends Assignable implements JsonSerializable, SceneHeadingInterface, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['context', 'sequence', 'setting'];

    public $contexts = ['I/E', 'INT', 'EXT', 'POV'];

    public $sequences = ['DAY', 'NIGHT', 'DAWN', 'DUSK', 'LATER', 'MOMENTS LATER', 'CONTINUOUS', 'MORNING', 'AFTERNOON', 'EVENING', 'THE NEXT DAY'];

    public $context;

    public $description;

    public $numbering;

    public $page;

    public $sequence;

    public $setting;

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
        unset ($data['required'], $data['contexts'], $data['sequences']);

        return $data;
    }
}
