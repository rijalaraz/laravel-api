<x-mail::message>
# Réinitialisation du mot de passe

{{ $greeting }}

{{ $line1 }}

<x-mail::button :url="$buttonUrl">
{{ $buttonText }}
</x-mail::button>

{{ $line2 }}

{{ $line3 }}

{{ $salutation }}

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
