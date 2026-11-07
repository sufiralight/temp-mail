<?php

namespace App\Http\Controllers;

use App\Installation\InstallationState;
use App\Installation\InstallationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InstallationController extends Controller
{
    public function __invoke(Request $request, InstallationStatus $installation): HttpResponse
    {
        $state = $installation->current();
        $headers = ['Cache-Control' => 'no-store'];

        if ($state === InstallationState::Installed) {
            return new RedirectResponse(route('login'), 302, $headers);
        }

        return new Response(
            view('install.index', [
                'state' => $state,
                'action' => $request->hasSession() ? route('install.database') : null,
                'csrfToken' => $request->hasSession() ? $request->session()->token() : null,
                'needsKey' => $request->hasSession() && ! $request->session()->get('operator_verified'),
                'values' => $request->hasSession() ? $request->session()->get('verified_database', []) : [],
            ]),
            $state === InstallationState::Uncertain ? 503 : 200,
            $headers,
        );
    }
}
