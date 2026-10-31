<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdministratorRequest;
use App\Http\Requests\DatabaseRequest;
use App\Installation\InstallationException;
use App\Installation\Installer;
use App\Installation\Requirements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallationController extends Controller
{
    public function welcome(): View
    {
        $checks = app(Requirements::class)->checks();

        return view('install.welcome', ['checks' => $checks, 'passed' => ! in_array(false, $checks, true)]);
    }

    public function database(): View
    {
        $this->requirements();

        return view('install.database');
    }

    public function probe(DatabaseRequest $request, Installer $installer): RedirectResponse
    {
        $this->requirements();
        $input = $request->validated();
        $input['db_password'] ??= '';
        try {
            $installer->probe($input, $request->session()->getId());
        } catch (InstallationException $exception) {
            return redirect('/install/database')->withErrors(['db_host' => __('install.errors.'.$exception->reason)])->withInput($request->only('db_host', 'db_port', 'db_name', 'db_user'));
        }

        return redirect('/install/admin');
    }

    public function admin(Request $request, Installer $installer): View
    {
        $installer->claimRecovery($request->session()->getId());
        $installer->owned($request->session()->getId());

        return view('install.admin');
    }

    public function saveAdmin(AdministratorRequest $request, Installer $installer): RedirectResponse
    {
        $installer->administrator($request->validated(), $request->session()->getId());

        return redirect('/install/complete');
    }

    public function review(Request $request, Installer $installer): View
    {
        $state = $installer->owned($request->session()->getId(), ['ready']);

        return view('install.complete', ['name' => $state['admin']['name'], 'email' => $state['admin']['email'], 'capabilities' => $state['capabilities']]);
    }

    public function complete(Request $request, Installer $installer): RedirectResponse
    {
        $installer->complete($request->session()->getId());
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function requirements(): void
    {
        if (! app(Requirements::class)->passed()) {
            throw new InstallationException('requirements', 422);
        }
    }
}
