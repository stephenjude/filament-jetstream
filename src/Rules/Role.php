<?php

namespace Filament\Jetstream\Rules;

use Closure;
use Filament\Jetstream\Jetstream;
use Illuminate\Contracts\Validation\ValidationRule;

class Role implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validRoles = collect(Jetstream::plugin()->getTeamRolesAndPermissions())->pluck('key')->toArray();

        if (!in_array($value, $validRoles)) {
            $fail(__('The :attribute must be a valid role.'));
        }
    }
}
