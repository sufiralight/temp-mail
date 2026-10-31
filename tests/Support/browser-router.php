<?php

// Development-only test harness. Never registered by production application routes.
use App\Installation\Database;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Tests\Support\FixtureDatabase;

$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ((str_starts_with($path, '/build/') || $path === '/favicon.ico') && is_file($root.'/public'.$path)) {
    return false;
}
// Model the public front controller; the PHP built-in router otherwise treats arbitrary
// requested filenames as SCRIPT_NAME and Symfony infers an incorrect base URL.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$directory = (string) getenv('BROWSER_FIXTURE_DIRECTORY');
if (! str_starts_with(str_replace('\\', '/', $directory), str_replace('\\', '/', $root.'/storage/framework/testing/browser-'))) {
    http_response_code(503);
    exit;
}
if (getenv('BROWSER_FIXTURE_DATABASE') === 'sqlite-workflow-fixture') {
    $app->instance(Database::class, new FixtureDatabase($directory.'/fixture.sqlite'));
}
$request = Request::capture();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
