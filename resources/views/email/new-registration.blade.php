<x-mail::message>
# Welcome to ROSSA

Your email is verified and your account is ready.

You can log in to submit studies to the archive or request API access for programmatic use.

<x-mail::button :url="route('data-submission')">
Explore Study Submission
</x-mail::button>

<x-mail::button :url="route('api-docs')">
View API Documentation
</x-mail::button>

</x-mail::message>
