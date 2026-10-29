<div>
     <div class="modal-header text-white" style="background-color: #0D3B52;">
        <h5 class="modal-title mb-0">{{ $procedure ? 'Edit Procedure' : 'Add a New Procedure' }}</h5>
        <button type="button" class="btn-close btn-close-white" wire:click="$dispatch('closeModal')" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <form wire:submit="saveProcedure">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="procedureType" class="form-label required">Procedure Type</label>
                    <select class="form-select @error('procedureType') is-invalid @enderror" 
                            id="procedureType" 
                            wire:model="procedureType">
                        <option value="">Select Procedure Type</option>
                        @foreach (\App\Enums\GonadectomyType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->name() }}</option>
                        @endforeach
                    </select>
                    @error('procedureType')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="age" class="form-label required">Age During Surgery (Weeks)</label>
                    <input type="number" 
                        class="form-control @error('age') is-invalid @enderror" 
                        id="age" 
                        wire:model="age" 
                        placeholder="Enter age">
                    @error('age')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                        id="description" 
                        wire:model="description"
                        rows="3"></textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit"
                    class="btn btn-primary"
                    wire:loading.attr="disabled"
                    title="{{ $procedure ? 'Update Procedure' : 'Add Procedure' }}"
                    data-bs-toggle="tooltip">
                    <span wire:loading.remove>
                        <i class="bi bi-{{ $procedure ? 'check-lg' : 'plus-lg' }}"></i>
                        {{ $procedure ? 'Update' : 'Add' }}
                    </span>
                    <span wire:loading>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ $procedure ? 'Updating...' : 'Adding...' }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>