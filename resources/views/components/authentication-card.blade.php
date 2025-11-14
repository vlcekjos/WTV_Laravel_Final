

<div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

<!-- VRSTVA 1: POZADÍ (Obrázek + Blur) -->
<div class="absolute inset-0 z-0 bg-bar-pozadi bg-cover bg-center bg-fixed blur-sm"></div>

<!-- VRSTVA 2: PŘEKRYTÍ (Ztmavení) -->
<div class="absolute inset-0 z-10 bg-black/60"></div>

<!-- VRSTVA 3: OBSAH (Formulář) -->
<div class="relative z-20">
    <!-- ZMĚNA 1: Odkaz loga nyní směřuje na 'mapa' místo '/' nebo 'dashboard' -->
    <a href="{{ route('mapa') }}">
        {{ $logo }}
    </a>
</div>

<!-- 
  ZMĚNA 2: Formulář rozšířen z 'sm:max-w-md' na 'sm:max-w-lg' 
  Zároveň jsem přidal zpět třídy z Figmy (černá/průhledná, žlutý okraj)
-->
<div class="relative z-20 w-full sm:max-w-lg mt-6 px-6 py-4 bg-black/75 shadow-lg overflow-hidden sm:rounded-lg">
    {{ $slot }}
</div>


</div>