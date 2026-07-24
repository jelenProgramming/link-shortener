<?php

namespace App\Console\Commands;

use App\Models\Click;
use Illuminate\Console\Command;

class PruneOldClicks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clicks:prune {--days=90 : Retention window in days}';

    /**
     * The console command description.
     */
    protected $description = 'Delete click records older than the retention window';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = Click::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Pruned {$deleted} click(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
