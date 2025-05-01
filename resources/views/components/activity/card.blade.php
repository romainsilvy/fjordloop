@props(['activity'])

<div class="bg-primary-500 rounded-md shadow-md">
    @if ($activity->getMediaDisplay() && count($activity->getMediaDisplay()) > 0)
        <div x-data="{
            currentIndex: 0,
            next() {
                if (this.currentIndex < {{ count($activity->getMediaDisplay()) }} - 1) {
                    this.currentIndex++;
                } else {
                    this.currentIndex = 0;
                }
            },
            prev() {
                if (this.currentIndex > 0) {
                    this.currentIndex--;
                } else {
                    this.currentIndex = {{ count($activity->getMediaDisplay()) }} - 1;
                }
            }
        }">
            <div class="relative">
                @if (count($activity->getMediaDisplay()) > 1)
                    <button type="button"
                        class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                        x-on:click="prev()">
                        <flux:icon.chevron-left />
                    </button>
                @endif
                <div class="carousel-container relative flex justify-center items-center overflow-hidden">
                    <template x-for="(media, index) in {{ $activity->getMediaDisplay() }}" :key="index">
                        <div class="w-full h-72 transition-all duration-500 bg-black/5" x-show="currentIndex === index">
                            <img :src="media.url" class="w-full h-full object-contain"
                                :alt="media.name || 'Activity image'" />
                        </div>
                    </template>
                </div>
                @if (count($activity->getMediaDisplay()) > 1)
                    <button type="button"
                        class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                        x-on:click="next()">
                        <flux:icon.chevron-right />
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="w-full flex justify-center items-center h-72 bg-black/5">
            <flux:icon.photo class="size-18 text-primary-400" />
        </div>
    @endif


    <div class="flex flex-col p-4">
        <h2 class="text-lg font-semibold text-zinc-800 dark:text-white">
            {{ $activity->name }}
        </h2>

        <p class="text-zinc-600 dark:text-zinc-300">
            {{ $activity->description }}
        </p>


        <p class="text-zinc-600 dark:text-zinc-300">
            {{ $activity->place_name }}
        </p>

        @if ($activity->url)
            <a href="{{ $activity->url }}" target="_blank"
                class="text-zinc-600 dark:text-zinc-300 underline w-min whitespace-nowrap">
                Voir le lien
            </a>
        @endif

        @if (isset($activity->price_by_person))
            <p class="text-zinc-600 dark:text-zinc-300">
                Prix par personne : @euro($activity->price_by_person)
            </p>
        @elseif (isset($activity->price_by_group))
            <p class="text-zinc-600 dark:text-zinc-300">
                Prix total : @euro($activity->price_by_group)
            </p>
        @endif

        <flux:spacer />

        <div class="flex mt-auto">
            <flux:spacer />
            <flux:icon.pencil-square class="cursor-pointer" wire:click="selectActivity('{{ $activity->id }}')" />
        </div>
    </div>
</div>
