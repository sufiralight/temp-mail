<?php

namespace App\Console\Commands;

use App\Installation\Installer;
use Illuminate\Console\Command;
use Throwable;

class RecoverInstallation extends Command
{
    protected $signature = 'install:recover {--reviewed : Confirm backup and same-target schema inspection}';

    protected $description = 'Reconcile an interrupted installation without rerunning partial migrations';

    public function handle(Installer $installer): int
    {
        if (! $this->option('reviewed')) {
            $this->error('Back up and inspect the original database and private state first.');

            return self::FAILURE;
        }
        try {
            $installer->recover();
            $this->info('State reconciled. Completed receipts remain locked; otherwise open /install/admin through the authorized access path and re-enter the administrator.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Recovery refused. Repair the original schema under operator review or restore matching backups; never reset the database or key.');

            return self::FAILURE;
        }
    }
}
