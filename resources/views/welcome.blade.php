<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="koleje" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-2">
                @foreach($colleges as $kolej)
                    <div class="kolej">
                        <span>{{ $kolej->nazev }}</span>
                        <div class="hodnoceni-obal">
                            <div 
                                class="hodnoceni-body"
                                style="height: {{ $pomocnaProm * $kolej->hodnoceni}}px; background: {{ $kolej->barva }}" 
                            ></div>
                        </div>
                        <span style="color: {{ $kolej->barva }}">{{ $kolej->hodnoceni }}</span>
                        <img src="{{ asset('images/' . $kolej->obrazek) }}" alt="{{ $kolej->nazev }}">
                    </div>                   
                @endforeach
            </div>
        </div>
    </div>
</x-guest-layout>
