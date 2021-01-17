<?php

namespace App\Services\ScreenJSON\Model\Document\Scene;

use App\Services\ScreenJSON\Interfaces\ElementInterface;

class Dialogue extends Element implements ElementInterface
{
    public $origins = ['V.O', 'O.S', 'O.C', 'FILTER'];

    public $type = 'dialogue';

    public $dual = false;

    public $origin;

    public function __construct ( ?string $id = null, ?array $assignable = null )
    {
        $this->__assign ($id, $assignable);
    }

}
