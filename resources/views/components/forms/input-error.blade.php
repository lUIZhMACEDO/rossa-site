@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'invalid-feedback']) }}>
        @foreach ((array) $messages as $message)
            <span class="d-block">{{ $message }}</span>
        @endforeach
    </div>
@endif
