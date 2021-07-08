<x-jet-action-section>
    <x-slot name="title">
        {{ __('Two Factor Authentication') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Add additional security to your account using two factor authentication.') }}
    </x-slot>

    <x-slot name="content">
        <h3 class="text-lg font-medium text-gray-900">
            @if ($this->enabled)
                {{ __('You have enabled two factor authentication.  Your QR Code is shown below.  You can rescan it if you lost your device.') }}
                <div class="mt-4">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>
            @else
                {{ __('You have not enabled two factor authentication.') }}
            @endif
        </h3>

        <div class="mt-3 max-w-xl text-sm text-gray-600">
            <p>
                {{ __('When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone\'s Google Authenticator application.') }}
            </p>
            <br>
            <p>
                {{ __('There are a number of popular authenticator apps available:') }}
            </p>
            <br>
            <ul>
            <li><a class = "btn btn-primary" href = "https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target = "_blank">Android Google Authenticator</a></li>
            <li><a class = "btn btn-primary" href = "https://play.google.com/store/apps/details?id=com.azure.authenticator" target = "_blank">Android Microsoft Authenticator</a></li>
            <li><a class = "btn btn-primary" href = "https://apps.apple.com/us/app/google-authenticator/id388497605" target = "_blank">IOS Google Authenticator</a></li>
            <li><a class = "btn btn-primary" href = "https://play.google.com/store/apps/details?id=com.azure.authenticator" target = "_blank">IOS Microsoft Authenticator</a></li>
            </ul>
        </div>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="mt-4 max-w-xl text-sm text-gray-600">
                    <p class="font-semibold">
                        {{ __('Two factor authentication is now enabled. Important !!  Scan the following QR code using your phone\'s authenticator application.  If you fail to do this, you may get locked out of your account and will have to contact support to unlock the account.  You can use the recovery codes below to get in if that happens, so please copy them.  You only have one chance to scan the QR Code.  If you fail to do so and have the recovery codes you can still log in to your account.') }}
                    </p>
                </div>

                <div class="mt-4">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>
            @endif

            @if ($showingRecoveryCodes)
                <div class="mt-4 max-w-xl text-sm text-gray-600">
                    <p class="font-semibold">
                        {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.') }}
                    </p>
                </div>

                <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-gray-100 rounded-lg">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-5">
            @if (! $this->enabled)
                <x-jet-button type="button" wire:click="enableTwoFactorAuthentication" wire:loading.attr="disabled">
                    {{ __('Enable') }}
                </x-jet-button>
            @else
                @if ($showingRecoveryCodes)
                    <x-jet-secondary-button class="mr-3" wire:click="regenerateRecoveryCodes">
                        {{ __('Regenerate Recovery Codes') }}
                    </x-jet-secondary-button>
                @else
                    <x-jet-secondary-button class="mr-3" wire:click="$toggle('showingRecoveryCodes')">
                        {{ __('Show Recovery Codes') }}
                    </x-jet-secondary-button>
                @endif

                <x-jet-danger-button wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                    {{ __('Disable') }}
                </x-jet-danger-button>
            @endif
        </div>
    </x-slot>
</x-jet-action-section>
