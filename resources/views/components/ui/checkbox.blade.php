@props(['name' => null, 'id' => null, 'label' => null, 'description' => null, 'error' => null, 'required' => false, 'disabled' => false, 'value' => '1', 'checked' => false])
<x-ui.choice :attributes="$attributes" type="checkbox" :$name :$id :$label :$description :$error :$required :$disabled :$value :$checked />
