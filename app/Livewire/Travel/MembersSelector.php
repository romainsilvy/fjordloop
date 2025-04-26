<?php

namespace App\Livewire\Travel;

use Livewire\Component;
use Livewire\Attributes\Modelable;
use Illuminate\Support\Facades\Validator;

class MembersSelector extends Component
{
    /** @var array<int, string> */
    #[Modelable]
    public array $members = [];

    public string $error = '';

    public string $memberToAdd = '';

    public function addMember(): void
    {
        $member = $this->memberToAdd;

        $validator = Validator::make(['email' => $member], [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            // Set the error message
            $this->error = $validator->errors()->first('email');

            return;
        }

        // Reset the error message
        $this->error = '';

        // Add the member if not already in the list
        if (! in_array($member, $this->members)) {
            $this->members[] = $member;
            $this->memberToAdd = '';
        }
    }

    public function deleteMember(int $index): void
    {
        unset($this->members[$index]);
    }
    public function render()
    {
        return view('livewire.travel.members-selector');
    }
}
