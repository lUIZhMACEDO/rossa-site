@component('mail::message')
# Study Submission Confirmed!

Your study has been successfully submitted to {{ config('app.name') }} and is now under administrative review.

**Title:** {{ $study->title ?? 'Untitled Study' }}

**Study ID:** {{ $study->identifier }}

@component('mail::button', ['url' => $studyUrl])
View Study
@endcomponent

You will be notified by email once your submission has been reviewed. In the meantime, your study is available in read-only mode.

If you need to make changes to your submission, please contact us at [{{ config('mail.rossa_contact_email') }}](mailto:{{ config('mail.rossa_contact_email') }}) before your study has been approved.

Thank you for contributing to open skeletal biology research.

— The {{ config('app.name') }} Team

@endcomponent
