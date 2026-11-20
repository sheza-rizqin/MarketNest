@props([
    'id',
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
])

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-ink">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class('block min-h-11 w-full rounded-xl border-sand bg-white px-3.5 text-sm text-ink placeholder:text-muted/70 focus:border-navy focus:ring-navy') }}
    >
    @error($name)
        <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
