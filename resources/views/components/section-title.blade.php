<div class="md:col-span-1 flex justify-between">
    <div class="px-4 sm:px-0">
        <!-- ZMĚNA: text-zluta (žlutý nadpis) -->
        <h3 class="text-lg font-medium text-zluta">{{ $title }}</h3>

        <!-- ZMĚNA: text-gray-400 (světle šedý popis) -->
        <p class="mt-1 text-sm text-gray-400">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>