@props([
    'label' => null,
    'name' => null,
])

<label class="switch">
    <input type="checkbox" name="{{ $name }}" {{ $attributes->except(['class']) }} />
    <span class="switch__track"></span>
    @if($label !== null)
        <span>{{ $label }}</span>
    @elseif(trim($slot) !== '')
        <span>{{ $slot }}</span>
    @endif
</label>
