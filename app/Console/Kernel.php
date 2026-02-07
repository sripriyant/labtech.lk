<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected array $commands = [
        \App\Console\Commands\ImportTestMastersCommand::class,
    ];
}
