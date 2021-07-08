<?php
use Spatie\Permission\Models\Permission;
?>
<x-jet-form-section submit="updateProfileInformation">
    <x-slot name="title">
        {{ __('Profile Information') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Update your account\'s profile information and email address.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Profile Photo -->
        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <div x-data="{photoName: null, photoPreview: null}" class="col-span-6 sm:col-span-4">
                <!-- Profile Photo File Input -->
                <input type="file" class="hidden"
                            wire:model="photo"
                            x-ref="photo"
                            x-on:change="
                                    photoName = $refs.photo.files[0].name;
                                    const reader = new FileReader();
                                    reader.onload = (e) => {
                                        photoPreview = e.target.result;
                                    };
                                    reader.readAsDataURL($refs.photo.files[0]);
                            " />                <x-jet-label for="photo" value="Photo" />

                <!-- Current Profile Photo -->
                <div class="mt-2" x-show="! photoPreview">
                    <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-full h-20 w-20 object-cover">
                </div>

                <!-- New Profile Photo Preview -->
                <div class="mt-2" x-show="photoPreview">
                    <span class="block rounded-full w-20 h-20"
                          x-bind:style="'background-size: cover; background-repeat: no-repeat; background-position: center center; background-image: url(\'' + photoPreview + '\');'">
                    </span>
                </div>

                <x-jet-secondary-button class="mt-2" type="button" x-on:click.prevent="$refs.photo.click()">
                    {{ __('Select A New Photo') }}
                </x-jet-secondary-button>

                <x-jet-input-error for="photo" class="mt-2" />
            </div>
        @endif

        <!-- Name -->
        <div class="col-span-4 sm:col-span-4">
            <x-jet-label for="name" value="Name" />
            <x-jet-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="state.name" autocomplete="name" />
            <x-jet-input-error for="name" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="col-span-4 sm:col-span-4">
            <x-jet-label for="email" value="Email" />
            <x-jet-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="state.email" />
            <x-jet-input-error for="email" class="mt-2" />
        </div>

         <!-- lname -->
        <div class="col-span-4 sm:col-span-4">
            <x-jet-label for="lname" value="Last Name" />
            <x-jet-input id="lname" type="text" class="mt-1 block w-full" wire:model.defer="state.lname" />
            <x-jet-input-error for="lname" class="mt-2" />
        </div>

        <!-- fname -->
        <div class="col-span-4 sm:col-span-4">
            <x-jet-label for="fname" value="First Name" />
            <x-jet-input id="fname" type="text" class="mt-1 block w-full" wire:model.defer="state.fname" />
            <x-jet-input-error for="fname" class="mt-2" />
        </div>

        <!-- mname -->
        <div class="col-span-4 sm:col-span-4">
            <x-jet-label for="mname" value="Middle Name" />
            <x-jet-input id="mname" type="text" class="mt-1 block w-full" wire:model.defer="state.mname" />
            <x-jet-input-error for="mname" class="mt-2" />
        </div>
        <!-- user_roles -->        
<!-- 
        <div class="col-span-4 sm:col-span-4 readonlydiv">
            <x-jet-label for="user_roles" value="user_roles:  " />
            <div><?php echo $state['user_roles'] ?></div>
        </div>
        
        <!~~ user_permissions ~~>        
        <div class="col-span-4 sm:col-span-4 readonlydiv">
            <x-jet-label for="user_permissions" value="user_permissions:  " />
            <div><?php echo json_encode(Auth::user()->getPermissionNames()) ?></div>
        </div>
        @if (Auth::user()->getPermissionNames()->contains('patient_data'))
        <!~~ patient_data ~~>
        <div class="col-span-4 sm:col-span-4 readonlydiv">
            <x-jet-label for="patientid" value="patientid" />
            <x-jet-input id="patientid" type="text" class="mt-1 block w-full" wire:model.defer="state.patientid" />
            <x-jet-input-error for="patientid" class="mt-2" />
        </div>
        @endif
        @if (Auth::user()->getPermissionNames()->contains('provider_data'))
        <!~~ provider_data ~~>
        <div class="col-span-4 sm:col-span-4 readonlydiv">
            <x-jet-label for="doctor_id" value="doctor_id:  " />
            <div><?php echo $state['doctor_id'] ?></div>
        </div>
        @endif
        @if (Auth::user()->getPermissionNames()->contains('reader_data'))
        <!~~ reader_id ~~>
        <div class="col-span-4 sm:col-span-4 readonlydiv">
            <x-jet-label for="reader_id" value="reader_id Principal Result Interpreter/NameOfPhysiciansReadingStudy/(0008,1060)" />
            <x-jet-input id="reader_id" type="text" class="mt-1 block w-full" wire:model.defer="state.reader_id" />
            <x-jet-input-error for="reader_id" class="mt-2" />
        </div>
        @endif
 -->
    </x-slot>

    <x-slot name="actions">
        <x-jet-action-message class="mr-3" on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <x-jet-button>
            {{ __('Save') }}
        </x-jet-button>
    </x-slot>
</x-jet-form-section>

<style>
.readonlydiv {
border-radius:5px;
border:solid 1px red;
padding:2px
}
</style>