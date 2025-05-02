<flux:field>
    <flux:label class="inline-flex items-center text-sm font-medium text-zinc-800">
        {{ $title }}
    </flux:label>

    <div
        class="w-full border rounded-lg block disabled:shadow-none appearance-none text-base sm:text-sm py-2 min-h-10 leading-[1.375rem] ps-3 pe-3 bg-white text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 shadow-xs border-zinc-200 border-b-zinc-300/80 disabled:border-b-zinc-200">
        <div class="flex items-center justify-between gap-2">
            <input type="text" wire:model="memberToAdd" placeholder="Ajouter un membre par e-mail"
                class="w-full" @keydown.enter.prevent="$wire.addMember()"></input>
            <span class="w-5 h-5 cursor-pointer" wire:click="addMember">+</span>
        </div>

        <div>
            @if (count($members) > 0)
                <hr class="border-zinc-200 mt-2">
            @endif
            @foreach ($members as $index => $member)
                <div class="flex justify-between items-center">
                    <p class="text-xs my-1">{{ $member }}</p>
                    <span class="w-5 h-5 my-1 text-grey" wire:click="deleteMember({{ $index }})">-</span>
                </div>
                @if (!$loop->last)
                    <hr class="border-zinc-200">
                @endif
                {{-- <hr> --}}
            @endforeach
        </div>
    </div>

    @if ($error)
        <p class="text-red-500 text-xs mt-1">{{ $error }}</p>
    @endif
</flux:field>
