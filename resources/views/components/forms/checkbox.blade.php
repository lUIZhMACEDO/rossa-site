<div>
    <div class="form-check">
		<input @class(['form-check-input', 'disabled' => $disabled])
			type="checkbox" 
			value="{{ $attributes->get('value') ? $attributes->get('value') : '1' }}"
			id="{{ $id }}"
			name="{{ $name }}"
			@if ( $model ) x-model="{{ $model }}" @endif @checked($checked) @disabled($disabled) />
		<label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
	</div>
</div>