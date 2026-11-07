<?php

namespace App\Http\Controllers;

use App\Installation\InstallationState;
use App\Installation\InstallationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class InstallationController extends Controller
{
    public function __invoke(InstallationStatus $installation): HttpResponse
    {
        $state = $installation->current();
        $headers = ['Cache-Control' => 'no-store'];

        if ($state === InstallationState::Installed) {
            return new RedirectResponse(url('/login'), 302, $headers);
        }

        return new Response(
            view('install.index', ['state' => $state]),
            $state === InstallationState::Uncertain ? 503 : 200,
            $headers,
        );
    }
}
