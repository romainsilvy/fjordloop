<x-mail::message>
Bonjour,

{{ $sender->name }} vous invite à rejoindre le voyage {{ $travel->name }}.

Cliquez sur le bouton ci-dessous pour accepter l'invitation.

<x-mail::button url="{{ route('travel.show', ['travelId' => $travel->id, 'token' => $invitationToken]) }}">
Cliquez ici
</x-mail::button>
</x-mail::message>
