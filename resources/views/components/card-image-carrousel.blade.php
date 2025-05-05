@props(['medias' => null, 'customHeight' => 'h-72'])

@if ($medias && count($medias) > 0)
    <div x-data="{
        currentIndex: 0,
        next() {
            if (this.currentIndex < {{ count($medias) }} - 1) {
                this.currentIndex++;
            } else {
                this.currentIndex = 0;
            }
        },
        prev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            } else {
                this.currentIndex = {{ count($medias) }} - 1;
            }
        }
    }">
        <div class="relative">
            @if (count($medias) > 1)
                <button type="button"
                    class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                    x-on:click="prev()">
                    <flux:icon.chevron-left />
                </button>
            @endif
            <div class="carousel-container relative flex justify-center items-center overflow-hidden">
                <template x-for="(media, index) in {{ $medias }}" :key="index">
                    <div class="w-full {{$customHeight}} transition-all duration-500 bg-black/5" x-show="currentIndex === index">
                        <img :src="media.url" class="w-full h-full object-contain"
                            :alt="media.name || 'Activity image'" />
                    </div>
                </template>
            </div>
            @if (count($medias) > 1)
                <button type="button"
                    class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
                    x-on:click="next()">
                    <flux:icon.chevron-right />
                </button>
            @endif
        </div>
    </div>
@else
    <div class="w-full {{$customHeight}} bg-primary-500 rounded-lg">
        <div class="w-full h-full flex justify-center items-center bg-black/5">
            <flux:icon.photo class="size-18 text-primary-400" />
        </div>
    </div>
@endif
