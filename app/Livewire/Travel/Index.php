<?php

namespace App\Livewire\Travel;

use App\Models\Travel;
use Livewire\Component;

class Index extends Component
{
    public $upcoming;
    public $active;
    public $past;
    public $noDate;
    public $isResultEmpty;

    public function mount()
    {
        $this->upcoming = Travel::upcoming()->get();
        $this->active = Travel::active()->get();
        $this->past = Travel::past()->get();
        $this->noDate = Travel::noDate()->get();

        $this->isResultEmpty = $this->upcoming->isEmpty() &&
            $this->active->isEmpty() &&
            $this->past->isEmpty() &&
            $this->noDate->isEmpty();
    }

    public function render()
    {
        return view('livewire.travel.index');
    }
}
