<?php

namespace App\Http\Controllers;

use App\Models\Travel;
use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function accept($token) {
        $travel = Travel::fromInvitation($token);

        if (!$travel) {
            return redirect()->route('travel.index')->error('Le lien d\'invitation est invalide.');
        }

        if ($travel->isMember()) {
            return redirect()->route('travel.show', $travel->id)->error('Vous êtes déjà membre de ce voyage.');
        }

        $travel->acceptInvitation($token);

        return redirect()->route('travel.show', $travel->id)->success('Vous avez rejoint le voyage.');
    }

    public function refuse($token) {
        $travel = Travel::fromInvitation($token);

        if (!$travel) {
            return redirect()->route('travel.index')->error('Le lien d\'invitation est invalide.');
        }

        if ($travel->isMember()) {
            return redirect()->route('travel.show', $travel->id)->error('Vous êtes déjà membre de ce voyage.');
        }

        $travel->refuseInvitation($token);
        return redirect()->route('travel.index')->success('Vous avez refusé l\'invitation au voyage.');
    }
}
