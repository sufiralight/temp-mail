<?php

namespace App\Providers;

use App\Installation\Database;
use App\Installation\InstallationException;
use App\Installation\InstallationStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('installation', fn ($request) => Limit::perMinute(60)->by('installer:'.$request->server('REMOTE_ADDR')));
        RateLimiter::for('installation-write', fn ($request) => Limit::perMinute(5)->by('installer-write:'.$request->path().':'.$request->server('REMOTE_ADDR')));
        // Bootstrap before application tables exist; never decrypt credentials into config cache.
        if (! $this->app->environment('testing')) {
            config(['session.driver' => 'file', 'session.encrypt' => true, 'cache.default' => 'file', 'queue.default' => 'sync']);
        }
        config(['session.domain' => null, 'session.http_only' => true, 'session.same_site' => 'strict']);
        config(['database.connections.installed' => ['driver' => 'installed']]);
        DB::extend('installed', function () {
            $state = app(InstallationStore::class)->read();
            if (! isset($state['database'])) {
                throw new InstallationException;
            }

            return app(Database::class)->connect($state['database']);
        });
        // Any installed application's future queries use the durable selected connection.
        if (is_file(app(InstallationStore::class)->directory().'/provisioned')) {
            config(['database.default' => 'installed']);
        }
    }
}
