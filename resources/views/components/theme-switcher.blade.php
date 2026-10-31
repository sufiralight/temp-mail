@php($group = 'theme-'.Illuminate\Support\Str::random(8))
<fieldset class="theme-switcher">
    <legend class="sr-only">{{ __('install.appearance') }}</legend>
    @foreach(['light' => 'sun', 'dark' => 'moon', 'system' => 'monitor'] as $choice => $icon)
    <label class="theme-option" data-tooltip>
        <input type="radio" name="{{ $group }}" value="{{ $choice }}" data-theme-choice aria-label="{{ __('install.theme.'.$choice) }}" @checked($choice === 'system')>
        <span class="theme-icon"><x-icon :name="$icon" /></span>
        <span class="tooltip" aria-hidden="true">{{ __('install.theme.'.$choice) }}</span>
    </label>
    @endforeach
</fieldset>
