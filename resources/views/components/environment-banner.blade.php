@if ( !app()->environment('production') )
<div class="py-2 text-bg-warning text-center w-100">
    <strong>Warning:</strong> You are in the <strong>{{ app()->environment() }}</strong> environment.
</div>
@endif
