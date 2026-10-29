@props(['upload'])

<form action="{{ route('upload.download', $upload) }}" method="POST">
    @csrf
    {{ $slot }}
</form>