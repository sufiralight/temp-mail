@if($errors->any())
<x-alert error="true" tabindex="-1" id="validation-summary">
    <h2>{{ __('install.correct_errors') }}</h2>
    <ul>@foreach($errors->messages() as $field => $messages)<li><a href="#{{ $field }}">{{ $messages[0] }}</a></li>@endforeach</ul>
</x-alert>
@endif
