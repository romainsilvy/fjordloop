@props(['images', 'existingImages' => []])

<div class="mt-4" x-data="{
    isScrollable: false,
    checkScroll() {
        const container = $refs.carousel;
        this.isScrollable = container.scrollWidth > container.clientWidth;
    },
    init() {
        this.checkScroll();

        // Optional: watch for DOM changes if needed
        const observer = new MutationObserver(() => this.checkScroll());
        observer.observe($refs.carousel, { childList: true, subtree: true });
    }
}" x-init="init()">
    <input type="file" id="image-upload" wire:model="images" multiple hidden x-ref="fileInput" />

    <label class="block text-sm font-medium text-gray-700 mb-2">Images</label>
    <div class="relative">
        <button type="button" class="absolute left-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
            x-show="isScrollable" x-on:click="$refs.carousel.scrollBy({left: -250, behavior: 'smooth'})">
            <flux:icon.chevron-left />
        </button>

        <button type="button"
            class="absolute right-0 top-1/2 -translate-y-1/2 bg-white/80 rounded-full p-2 shadow z-10"
            x-show="isScrollable" x-on:click="$refs.carousel.scrollBy({left: 250, behavior: 'smooth'})">
            <flux:icon.chevron-right />
        </button>

        <div class="carousel-container flex gap-4 overflow-x-auto pb-4 scrollbar-hide snap-x" x-ref="carousel">
            @foreach ($existingImages as $index => $image)
                @if ($image['marked_for_deletion'])
                    @continue
                @endif

                <div class="relative flex-none w-40 h-40 snap-start">
                    <img src="{{ $image['url'] }}" class="w-full h-full object-cover rounded-lg shadow"
                        alt="{{ $image['name'] }}" />

                    <button type="button" wire:click="markMediaForDeletion({{ $image['id'] }})"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 transition">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            @endforeach

            @foreach ($images as $index => $image)
                <div class="relative flex-none w-40 h-40 snap-start">
                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover rounded-lg shadow"
                        alt="Uploaded image {{ $index + 1 }}" />

                    <button type="button" wire:click="removeImage({{ $index }})"
                        class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 transition">
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            @endforeach

            <div class="flex-none w-40 h-40 snap-start">
                <button type="button" x-on:click="$refs.fileInput.click()"
                    class="w-full h-full flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg text-gray-400 hover:text-gray-500 hover:border-gray-400 transition">
                    <flux:icon.plus class="size-12" />
                </button>
            </div>
        </div>
    </div>
</div>
