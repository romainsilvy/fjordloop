<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;

class Index extends Component
{
    public $sections = [];

    public function mount()
    {
        $upcoming = Travel::upcoming()->get();
        $active = Travel::active()->get();
        $past = Travel::past()->get();
        $noDate = Travel::noDate()->get();

        if ($active->isNotEmpty()) {
            $this->sections[] = ['title' => 'Actifs', 'travels' => $active];
        }

        if ($upcoming->isNotEmpty()) {
            $this->sections[] = ['title' => 'À venir', 'travels' => $upcoming];
        }

        if ($past->isNotEmpty()) {
            $this->sections[] = ['title' => 'Passés', 'travels' => $past];
        }

        if ($noDate->isNotEmpty()) {
            $this->sections[] = ['title' => 'Pas de date renseignée', 'travels' => $noDate];
        }
    }

    public function render()
    {
        return view('livewire.travel.index');
    }
}
