@php
    use App\Installation\InstallationState;
@endphp

@switch($state)
    @case(InstallationState::Fresh)
        <x-installation-layout title="Connect your database">
            <p>Use the database connection details from your hosting control panel.</p>
        </x-installation-layout>
        @break

    @case(InstallationState::Recovery)
        <x-installation-layout title="Restore configuration">
            <p>Restore your configuration from a backup to access your existing workspace.</p>
        </x-installation-layout>
        @break

    @case(InstallationState::Uncertain)
        <x-installation-layout title="Installation status unavailable">
            <p>Check the installation state and configuration in your hosting control panel before continuing.</p>
        </x-installation-layout>
        @break
@endswitch
