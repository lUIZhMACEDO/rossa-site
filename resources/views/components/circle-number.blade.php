
<span {{ $attributes->merge(['class' => 'flex-shrink-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-fira', 'style' => 'width: 32px; height: 32px;']) }}>
    <span class="small fw-semibold">
        {{ $slot }}
    </span>
</span>