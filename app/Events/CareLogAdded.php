<?php

namespace App\Events;

use App\Models\CareLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CareLogAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly CareLog $careLog) {}
}
