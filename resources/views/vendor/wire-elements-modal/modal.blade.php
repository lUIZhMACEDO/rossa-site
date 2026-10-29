<div>
    <div x-data="LivewireUIModal()"
        x-cloak
        x-ref="modal"
        x-init="
            const bsModal = new bootstrap.Modal($refs.modal, {
                backdrop: 'static',
                keyboard: false
            });
            $watch('show', value => {
                if (value) {
                    bsModal.show();
                } else {
                    bsModal.hide();
                }
            });
            $refs.modal.addEventListener('hidden.bs.modal', () => show = false);
        "
        class="modal fade"
        role="dialog"
        aria-labelledby="modalLabel"
        aria-hidden="true"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" :class="modalWidth">
            <div class="modal-content">
                @forelse($components as $id => $component)
                    <div x-show.immediate="activeComponent == '{{ $id }}'" x-ref="{{ $id }}" wire:key="{{ $id }}">
                        @livewire($component['name'], $component['arguments'], key($id))
                    </div>
                @empty
                @endforelse
            </div>
        </div>
    </div>
</div>
