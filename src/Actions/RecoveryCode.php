<?php

namespace Filament\Jetstream\Actions;

use Illuminate\Support\Str;

class RecoveryCode
{
    /**
     * Generate a new recovery code.
     *
     * @return string
     */
    public static function generate()
    {
        return Str::random(10) . '-' . Str::random(10);
    }
}
