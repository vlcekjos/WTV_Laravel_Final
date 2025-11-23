<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <!-- ZMĚNA: text-gray-900 -> text-zluta (Nadpis žlutý) -->
        <h3 class="text-lg font-medium text-zluta">{{ $title }}</h3>

        <!-- ZMĚNA: text-gray-600 -> text-gray-400 (Popis světle šedý) -->
        <p class="mt-1 text-sm text-gray-400">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>