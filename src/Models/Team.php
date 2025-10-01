<?php

namespace Filament\Jetstream\Models;

use Filament\Jetstream\Events\TeamCreated;
use Filament\Jetstream\Events\TeamDeleted;
use Filament\Jetstream\Events\TeamUpdated;
use Filament\Jetstream\Jetstream;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'personal_team' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Model $team) {
            $team->slug = static::generateUniqueSlug($team->name);
        });

        static::updating(function (Model $team) {
            // Update slug when name is changed.
            if ($team->isDirty('name')) {
                $team->slug = static::generateUniqueSlug($team->name, $team->id);
            }
        });
    }


    /**
     * Generates a unique slug from the given name, incrementing a counter if the slug already exists.
     *
     * @param string $name The name to generate the slug from.
     * @param null|int $ignoreId The ID of the model to ignore when checking for existing slugs.
     * @return string The unique slug.
     */
    protected static function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $slug = \Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }


    /**
     * Get the owner of the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        $model = Jetstream::plugin()->userModel();

        $foreignKey = Jetstream::getForeignKeyColumn($model);

        return $this->belongsTo($model, $foreignKey);
    }

    /**
     * Get all of the team's users including its owner.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all of the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        $userModel = Jetstream::plugin()->userModel();

        $membershipModel = Jetstream::plugin()->membershipModel();

        $foreignPivotKey = Jetstream::getForeignKeyColumn(Jetstream::plugin()->teamModel());

        $relatedPivotKey = Jetstream::getForeignKeyColumn($userModel);

        return $this->belongsToMany(
            $userModel,
            $membershipModel,
            $foreignPivotKey,
            $relatedPivotKey,
        )
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the given user belongs to the team.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function hasUser($user)
    {
        return $this->users->contains($user) || $user->ownsTeam($this);
    }

    /**
     * Determine if the given email address belongs to a user on the team.
     *
     * @return bool
     */
    public function hasUserWithEmail(string $email)
    {
        return $this->allUsers()->contains(function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    /**
     * Determine if the given user has the given permission on the team.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     * @return bool
     */
    public function userHasPermission($user, $permission)
    {
        return $user->hasTeamPermission($this, $permission);
    }

    /**
     * Get all of the pending user invitations for the team.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teamInvitations()
    {
        $foreignKey = Jetstream::getForeignKeyColumn(Jetstream::plugin()->teamModel());

        return $this->hasMany(Jetstream::plugin()->teamInvitationModel(), $foreignKey);
    }

    /**
     * Remove the given user from the team.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function removeUser($user)
    {
        if ($user->current_team_id === $this->id) {
            $user->forceFill([
                'current_team_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all of the team's resources.
     *
     * @return void
     */
    public function purge()
    {
        $this->owner()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->detach();

        $this->delete();
    }
}
