@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <!-- 
                Hlavní část formuláře (inputy)
                Změna: Pokud existují akce (tlačítka), odstraníme spodní border, aby to splynulo 
            -->
            <div class="px-4 py-5 bg-black/75 border-x border-t border-zluta sm:p-6 shadow {{ isset($actions) ? 'sm:rounded-tl-md sm:rounded-tr-md border-b-0' : 'sm:rounded-md border-b' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <!-- 
                    Spodní část formuláře (tlačítka)
                    ZMĚNA: Odstraněno 'border-t border-zluta'.
                    Nyní má jen postranní a spodní okraj, takže vizuálně navazuje na horní část.
                -->
                <div class="flex items-center justify-end px-4 py-3 bg-black/75 border-x border-b border-zluta text-end sm:px-6 shadow sm:rounded-bl-md sm:rounded-br-md">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>