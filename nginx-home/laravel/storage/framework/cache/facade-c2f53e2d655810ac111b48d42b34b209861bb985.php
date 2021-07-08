<?php

namespace Facades\Livewire;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Livewire\GenerateSignedUploadUrl
 */
class GenerateSignedUploadUrl extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Livewire\GenerateSignedUploadUrl';
    }
}
