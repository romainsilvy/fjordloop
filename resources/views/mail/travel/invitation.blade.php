<x-mail::message>
Bonjour,

{{ $sender->name }} vous invite à rejoindre le voyage {{ $travel->name }}.

Cliquez sur les boutons ci-dessous pour accepter ou refuser l'invitation.

<x-mail::button url="{{ route('travel.invitation.accept', ['token' => $invitationToken]) }}">
Accepter l'invitation
</x-mail::button>

<x-mail::button url="{{ route('travel.invitation.refuse', ['token' => $invitationToken]) }}">
Refuser l'invitation
</x-mail::button>
</x-mail::message>
