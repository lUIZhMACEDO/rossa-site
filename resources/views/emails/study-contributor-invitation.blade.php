@component('mail::message')
# Study collaboration invitation

{{ $invitation->inviter->display_name ?: $invitation->inviter->email }} has invited you to collaborate on a study in {{ config('app.name') }}.

**Study:** {{ $invitation->study->title ?: 'Untitled Study' }}

Contributors can manage this study, including its study information and uploaded data.

@if($invitation->user_id)
Sign in to your ROSSA account to accept or decline this invitation.
@else
You don't have a ROSSA account yet. To accept, first [create an account]({{ route('register.show') }}) using this email address (**{{ $invitation->email }}**) and verify it. Then return to this email and click the button below.
@endif

This link expires on {{ $invitation->expires_at->toFormattedDateString() }}.

@component('mail::button', ['url' => $acceptUrl])
Review invitation
@endcomponent

If you were not expecting this invitation, you can safely ignore this email.

— The {{ config('app.name') }} Team
@endcomponent