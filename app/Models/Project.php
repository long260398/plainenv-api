<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Project extends Model
{
    protected $fillable = ['user_id', 'name', 'description'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function environments(): HasMany
    {
        return $this->hasMany(Environment::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function variables(): HasManyThrough
    {
        return $this->hasManyThrough(Variable::class, Environment::class);
    }

    public function hasAccess(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->members()->where('user_id', $user->id)->exists();
    }

    public function getMemberRole(User $user): ?string
    {
        if ($this->user_id === $user->id) {
            return 'owner';
        }
        return $this->members()->where('user_id', $user->id)->value('role');
    }
}
