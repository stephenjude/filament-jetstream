<?php

namespace Filament\Jetstream\Models;

use Filament\Jetstream\Jetstream;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Membership extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'team_user';

    public function user(): BelongsTo
    {
        return $this->belongsTo(Jetstream::plugin()->userModel);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::plugin()->teamModel);
    }
}
