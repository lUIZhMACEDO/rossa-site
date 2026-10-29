@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->class([
    'form-control',
    'is-invalid' => $errors->has($attributes->get('name')) || $errors->updatePassword->has($attributes->get('name')),
])->merge(
    ['value' => old($attributes->get('name'))]
) }}>
