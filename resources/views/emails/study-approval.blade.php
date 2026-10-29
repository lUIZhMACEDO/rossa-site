@component('mail::message')
# Study Approved

Congratulations! Your study has been reviewed and approved by the {{ config('app.name') }} team and is now publicly accessible in the repository.

**Title:** {{ $study->title ?? 'Untitled Study' }}

**Study ID:** {{ $study->identifier }}

@component('mail::button', ['url' => $studyUrl])
View Study
@endcomponent

If you have any questions or need to make changes to your study, please contact us at [{{ config('mail.rossa_contact_email') }}](mailto:{{ config('mail.rossa_contact_email') }}).

Thank you for contributing to open skeletal biology research.

— The {{ config('app.name') }} Team

@endcomponent
