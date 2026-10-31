<?php

use App\Installation\InstallationException;
use App\Installation\Installer;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$path = (string) getenv('INSTALLATION_DIRECTORY');
if (! str_contains(str_replace('\\', '/', $path), '/storage/framework/testing/temp_mail_test_')) {
    exit(1);
}
fwrite(STDOUT, "ready\n");
fflush(STDOUT);
fgets(STDIN);
try {
    app(Installer::class)->complete('process-owner');
    echo 'completed';
} catch (InstallationException $exception) {
    echo $exception->reason;
}
