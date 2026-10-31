@props(['step'])
<nav aria-label="{{ __('install.progress') }}">
<ol class="steps">
@foreach(['requirements', 'database', 'administrator', 'complete'] as $key)
    <li @if($loop->iteration === (int) $step) aria-current="step" @endif>
        <span class="step-number">0{{ $loop->iteration }}</span>{{ __('install.short.'.$key) }}
    </li>
@endforeach
</ol>
</nav>
