@props(['submit'])

<div {{ $attributes->merge(['class' => 'md:grid md:grid-cols-3 md:gap-6']) }}>
    <x-section-title>
        <x-slot name="title">{{ $title }}</x-slot>
        <x-slot name="description">{{ $description }}</x-slot>
    </x-section-title>

    <div class="mt-5 md:mt-0 md:col-span-2">
        <form wire:submit="{{ $submit }}">
            <!-- 
                HLAVNÍ ČÁST FORMULÁŘE 
                - bg-black (černé pozadí)
                - border-t, border-x, border-zluta (žluté okraje nahoře a po stranách)
                - rounded-t-lg (zaoblené horní rohy)
                - Pokud formulář NEMA tlačítka (actions), border i dolů a zaoblit i spodek.
            -->
            <div class="px-4 py-5 bg-black sm:p-6 shadow {{ isset($actions) ? 'sm:rounded-t-lg border-t border-x border-zluta' : 'sm:rounded-lg border border-zluta' }}">
                <div class="grid grid-cols-6 gap-6">
                    {{ $form }}
                </div>
            </div>

            @if (isset($actions))
                <!-- 
                    SPODNÍ ČÁST (TLAČÍTKA)
                    - bg-black (černé pozadí)
                    - border border-zluta (kompletní rámeček, ale horní se překryje s vrškem)
                    - sm:rounded-b-lg (zaoblené spodní rohy)
                -->
                <div class="flex items-center justify-end px-4 py-3 bg-black text-end sm:px-6 shadow sm:rounded-b-lg border-b border-x border-zluta">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>
</div>