<x-mail::message>
# Vérification de votre e-mail

{{ $greeting }}

{{ $line1 }}

<x-mail::button :url="$buttonUrl">
{{ $buttonText }}
</x-mail::button>

{{ $line2 }}

{{ $salutation }},<br>
{{ config('app.name') }}
</x-mail::message>
