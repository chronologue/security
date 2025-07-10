<?php

namespace Chronologue\Security\Database\Eloquent;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'users';
    protected $guarded = ['id'];
    protected $visible = ['id', 'name', 'email', 'permissions'];
    protected $perPage = 10;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->setAttribute('password', Str::password());
        });
    }

    public function permitted(string|array $permission): bool
    {
        $permission = is_string($permission) ? [$permission] : $permission;
        $permissions = collect($this->getAttribute('permissions') ?? []);
        return $permissions->intersect($permission)->count() === count($permission);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }
}
