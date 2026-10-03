<?php

declare(strict_types=1);

namespace MaioBarberoDefault\MaioBarberoDefault\Console\Commands;

use Illuminate\Console\Command;

class MaioBarberoDefaultCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'default:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package default.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('MaioBarberoDefault placeholder command executed.');

        return self::SUCCESS;
    }
}
