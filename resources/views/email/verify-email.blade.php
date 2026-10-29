<x-mail::message>
# Verify your ROSSA account

Welcome to ROSSA, the Rodent Open Science Skeletal Archive.

Please confirm your email address. Open the link below, then click **Verify email address** on the confirmation page.

<x-mail::button :url="$url">
Verify email address
</x-mail::button>

This link expires in {{ $expireMinutes }} minutes.

If you did not create a ROSSA account, you can ignore this email.

</x-mail::message>
