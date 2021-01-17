<?php

namespace App\Services\ScreenJSON\Model\Document\Scene;

use App\Services\ScreenJSON\Interfaces\ElementInterface;

class General extends Element implements ElementInterface
{
    public $type = 'general';

    public function __construct ( ?string $id = null, ?array $assignable = null )
    {
        $this->__assign ($id, $assignable);
    }

}
