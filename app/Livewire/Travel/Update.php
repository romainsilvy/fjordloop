<?php

namespace App\Livewire\Travel;

use Flux\Flux;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Livewire\Attributes\Validate;

class Update extends Component
{
    public $travel;

    #[Validate('required')]
    public $name;

    public $place = [
        'display_name' => null,
        'lat' => null,
        'lng' => null,
        'geojson' => null,
    ];

    public $membersToInvite = [];

    public array $dateRange = [
        'start' => null,
        'end' => null,
    ];

    public $invitations;
    public $members;

    public function mount($travel)
    {
        $this->travel = $travel;


        $this->loadFields();
    }

    public function cleanupFields()
    {
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->name = $this->travel->name;

        $this->place['display_name'] = $this->travel->place_name;
        $this->place['lat'] = $this->travel->place_latitude;
        $this->place['lng'] = $this->travel->place_longitude;
        $this->place['geojson'] = $this->travel->place_geojson;
        $this->dateRange['start'] = $this->travel->start_date;
        $this->dateRange['end'] = $this->travel->end_date;
        $this->membersToInvite = [];

        $this->invitations = $this->travel->invitations()->get();
        $this->members = $this->travel->members()->get();
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();

        $this->travel->update([
            'name' => $this->name,
            'place_name' => $this->place['display_name'],
            'place_latitude' => $this->place['lat'],
            'place_longitude' => $this->place['lng'],
            'place_geojson' => $this->place['geojson'],
            'start_date' => $this->dateRange['start'],
            'end_date' => $this->dateRange['end'],
        ]);

        $this->travel->inviteMembers($this->membersToInvite, $user);

        Toaster::success('Voyage modifié!');
        return redirect()->route('travel.show', [
            'travelId' => $this->travel->id,
        ]);
    }

    public function deleteMember($memberId)
    {
        $this->travel->members()->detach($memberId);
        $this->members = $this->travel->members()->get();
        Toaster::success('Membre supprimé!');
    }

    public function deleteInvitation($invitationId)
    {
        $this->travel->invitations()->where('id', $invitationId)->delete();
        $this->invitations = $this->travel->invitations()->get();
        Toaster::success('Invitation supprimée!');
    }

    public function resendInvitation($invitationId)
    {
        $invitation = $this->travel->invitations()->where('id', $invitationId)->first();
        if ($invitation) {
            $invitation->sendEmail();
            Toaster::success('Invitation renvoyée!');
        } else {
            Toaster::error('Invitation non trouvée!');
        }
    }

    public function render()
    {
        return view('livewire.travel.update');
    }
}
