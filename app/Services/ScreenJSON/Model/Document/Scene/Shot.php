<?php

namespace App\Services\ScreenJSON\Model\Document\Scene;

use App\Services\ScreenJSON\Interfaces\ElementInterface;

class Shot extends Element implements ElementInterface
{
    public $type = 'shot';

    public function __construct ( ?string $id = null, ?array $assignable = null )
    {
        $this->__assign ($id, $assignable);
    }

}
