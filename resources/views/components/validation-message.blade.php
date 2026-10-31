@props(['name'])
@error($name)<p id="{{ $name }}-error" class="field-error">{{ $message }}</p>@enderror
