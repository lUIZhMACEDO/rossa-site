<select {{ $attributes->class([
    'form-select',
    'is-invalid' => $errors->has($attributes->get('name')),
]) }}>
    {{ $slot }}
</select>