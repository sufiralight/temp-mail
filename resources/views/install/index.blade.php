@php
    use App\Installation\InstallationState;
@endphp

@switch($state)
    @case(InstallationState::Fresh)
        @include('install.database')
        @break

    @case(InstallationState::Recovery)
        @include('install.recovery')
        @break

    @case(InstallationState::Uncertain)
        <x-installation-layout
            title="Installation status unavailable"
            description="Check your private installation state and configuration in your hosting control panel before continuing."
        />
        @break
@endswitch
