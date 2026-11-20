@props([
    'id',
    'name',
    'label',
    'options' => [],
    'placeholder' => 'Choose an option',
    'value' => '',
])

<div>
    <label for="{{ $id }}" class="mb-1.5 block text-sm font-medium text-ink">{{ $label }}</label>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        {{ $attributes->class('block min-h-11 w-full rounded-xl border-sand bg-white px-3.5 text-sm text-ink focus:border-navy focus:ring-navy') }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
