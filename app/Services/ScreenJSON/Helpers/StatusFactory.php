<?php

namespace App\Services\ScreenJSON\Helpers;

use App\Services\ScreenJSON\Model\Workflow\Status;

use \Carbon\Carbon;

class StatusFactory
{
    public $colors = ['white', 'blue', 'pink', 'yellow', 'green', 'goldenrod', 'buff', 'salmon', 'cherry'];

    public function create ( ?int $round = 1 ) : array
    {
        $output = [];

        foreach ($this->colors AS $id)
        {
            $output[$id] = new Status ($id, $round, Carbon::now()->format ('c'));
        }

        return $output;
    }
}
