@props(['medias' => null, 'customHeight' => 'h-72'])

@if ($medias && count($medias) > 0)
<div wire:ignore x-data="{
    currentIndex: 0,
    images: {{ json_encode($medias) }},
    next() {
        if (this.currentIndex < this.images.length - 1) {
            this.currentIndex++;
        } else {
            this.currentIndex = 0;
        }
    },
    prev() {
        if (this.currentIndex > 0) {
            this.currentIndex--;
        } else {
            this.currentIndex = this.images.length - 1;
        }
    },
    updateImages(newImages) {
        this.images = newImages;
        this.currentIndex = 0;
    }
}"
x-on:media-refreshed.window="updateImages($event.detail[0])"
>
    <div class="relative">
        <button type="button"
            x-show="images.length > 1"
            class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
            x-on:click="prev()"
            aria-label="Image précédente"
            @keydown.enter="prev()"
            @keydown.space.prevent="prev()">
            <flux:icon.chevron-left />
        </button>
        <div class="carousel-container relative flex justify-center items-center overflow-hidden">
            <template x-for="(media, index) in images" :key="index">
                <div class="w-full {{ $customHeight }} transition-all duration-500 bg-black/5"
                     x-show="currentIndex === index"
                     role="img"
                     :aria-label="media.name || 'Image de l\'activité'"
                     :aria-describedby="'image-' + index">
                    <img :src="media.url" class="w-full h-full object-contain"
                        :alt="media.name || 'Activity image'" />
                </div>
            </template>
        </div>
        <button type="button"
            x-show="images.length > 1"
            class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
            x-on:click="next()"
            aria-label="Image suivante"
            @keydown.enter="next()"
            @keydown.space.prevent="next()">
            <flux:icon.chevron-right />
        </button>
    </div>
</div>
@else
<div class="w-full {{ $customHeight }} bg-primary-500 rounded-lg">
    <div class="w-full h-full flex justify-center items-center bg-black/5">
        <flux:icon.photo class="size-18 text-primary-400" />
    </div>
</div>
@endif
