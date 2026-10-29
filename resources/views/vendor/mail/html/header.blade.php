@props(['url', 'message' => null])
@php
    $logoPath = public_path('images/ROSSA-logo-email.png');
    $logoSrc = ($message && method_exists($message, 'embed'))
        ? ($message->embed($logoPath) ?: asset('images/ROSSA-logo-email.png'))
        : asset('images/ROSSA-logo-email.png');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logoSrc }}" class="logo" alt="ROSSA Logo" width="273" height="75">
</a>
</td>
</tr>
