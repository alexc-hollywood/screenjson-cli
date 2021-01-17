<?php

namespace App\Services\ScreenJSON\Model\Workflow;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;

use App\Services\ScreenJSON\Interfaces\MetaObjectInterface;

use App\Services\ScreenJSON\Exceptions\InvalidParameterException;

use App\Services\ScreenJSON\Traits\AllowsMetaObject;

class Status extends Assignable implements JsonSerializable, MetaObjectInterface
{
    use AllowsMetaObject;

    public $required = ['color', 'round', 'updated'];

    public $colors = ['white', 'blue', 'pink', 'yellow', 'green', 'goldenrod', 'buff', 'salmon', 'cherry', 'tan'];

    public $color;

    public $round;

    public $updated;

    public function __construct ( string $color, int $round, string $updated )
    {
        if ( empty ($color) )
        {
            throw new InvalidParameterException ("Script color cannot be blank.");
        }

        if (! preg_match ('/^[\p{L}\p{N}_-]+$/u', $color) )
        {
            throw new InvalidParameterException ("Script color may only contain letters, numbers, hyphens, and/or underscores.");
        }

        if (! in_array ($color, $this->colors) )
        {
            throw new InvalidParameterException ("Script color is invalid. Allowed: ".implode ('|', $this->colors));
        }

        if ( $round < 1 || $round > 100 )
        {
            throw new InvalidParameterException ("Script publication round must be between 1 and 100.");
        }

        if ( empty ($updated) || !strtotime ($updated) )
        {
            throw new InvalidParameterException ("Script publication time must be a valid time/date.");
        }

        $this->color = $color;

        $this->round = $round;

        $this->updated = $updated;
    }

    public function jsonSerialize ()
    {
        $data = get_object_vars ($this);
        unset ($data['required'], $data['colors']);

        return $data;
    }
}
