<?php

namespace App\Models;

use App\Scopes\HostScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerifications extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // See App\Scopes\HostScope — default-filters to host scope (0,0).
        static::addGlobalScope(new HostScope());
    }
}
