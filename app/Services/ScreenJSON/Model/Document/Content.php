<?php

namespace App\Services\ScreenJSON\Model\Document;

use App\Services\ScreenJSON\Model\Assignable;

use \JsonSerializable;
use App\Services\ScreenJSON\Interfaces\MultiLingualInterface;

class Content extends Assignable implements JsonSerializable, MultiLingualInterface
{
    public function __construct ( ?array $assignable = null )
    {
        if ( $assignable && count ($assignable) )
        {
            foreach ($assignable AS $key => $value)
            {
                $this->{$key} = $value;
            }
        }
    }

    public function jsonSerialize ()
    {
        return $this;
    }
}
