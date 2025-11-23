<x-action-section>
    <x-slot name="title">
        {{ __('Dvoufázové ověření') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Zvyšte bezpečnost svého účtu pomocí dvoufázového ověření.') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-zluta">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('Dokončete aktivaci dvoufázového ověření.') }}
                @else
                    {{ __('Máte zapnuté dvoufázové ověření.') }}
                @endif
            @else
                {{ __('Nemáte zapnuté dvoufázové ověření.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-400">
            <p>
                {{ __('Když je dvoufázové ověření povoleno, budete při přihlašování vyzváni k zadání bezpečného náhodného tokenu. Tento token můžete získat z aplikace Google Authenticator ve svém telefonu.') }}
            </p>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="mt-4 max-w-xl text-sm text-gray-400">
                    <p class="font-semibold">
                        @if ($showingConfirmation)
                            {{ __('Pro dokončení aktivace dvoufázového ověření naskenujte následující QR kód pomocí ověřovací aplikace v telefonu nebo zadejte klíč nastavení a uveďte vygenerovaný OTP kód.') }}
                        @else
                            {{ __('Dvoufázové ověření je nyní povoleno. Naskenujte následující QR kód pomocí ověřovací aplikace v telefonu nebo zadejte klíč nastavení.') }}
                        @endif
                    </p>
                </div>

                <div class="mt-4 p-2 inline-block bg-white">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-4 max-w-xl text-sm text-gray-400">
                    <p class="font-semibold">
                        {{ __('Klíč nastavení') }}: {{ decrypt($this->user->two_factor_secret) }}
                    </p>
                </div>

                @if ($showingConfirmation)
                    <div class="mt-4">
                        <x-label for="code" value="{{ __('Kód') }}" />

                        <x-input id="code" type="text" name="code" class="block mt-1 w-1/2" inputmode="numeric" autofocus autocomplete="one-time-code"
                            wire:model="code"
                            wire:keydown.enter="confirmTwoFactorAuthentication" />

                        <x-input-error for="code" class="mt-2" />
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="mt-4 max-w-xl text-sm text-gray-400">
                    <p class="font-semibold">
                        {{ __('Uložte tyto obnovovací kódy do bezpečného správce hesel. Lze je použít k obnovení přístupu k účtu v případě ztráty zařízení pro dvoufázové ověření.') }}
                    </p>
                </div>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-900 rounded-lg">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div class="text-gray-100">{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5">
            @if (! $this->enabled)
                <x-confirms-password wire:then="enableTwoFactorAuthentication">
                    <x-button type="button" wire:loading.attr="disabled">
                        {{ __('Zapnout') }}
                    </x-button>
                </x-confirms-password>
            @else
                @if ($showingRecoveryCodes)
                    <x-confirms-password wire:then="regenerateRecoveryCodes">
                        <x-secondary-button class="me-3">
                            {{ __('Vygenerovat nové obnovovací kódy') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @elseif ($showingConfirmation)
                    <x-confirms-password wire:then="confirmTwoFactorAuthentication">
                        <x-button type="button" class="me-3" wire:loading.attr="disabled">
                            {{ __('Potvrdit') }}
                        </x-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="showRecoveryCodes">
                        <x-secondary-button class="me-3">
                            {{ __('Zobrazit obnovovací kódy') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @endif

                @if ($showingConfirmation)
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-secondary-button wire:loading.attr="disabled">
                            {{ __('Zrušit') }}
                        </x-secondary-button>
                    </x-confirms-password>
                @else
                    <x-confirms-password wire:then="disableTwoFactorAuthentication">
                        <x-danger-button wire:loading.attr="disabled">
                            {{ __('Vypnout') }}
                        </x-danger-button>
                    </x-confirms-password>
                @endif
            @endif
        </div>
    </x-slot>
</x-action-section>