<?php

namespace App\Models;

use App\Scopes\HostScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        // Per-storefront identity scoping. Default-filters to host (0,0); a
        // storefront request filters to its scope; backend guards bypass.
        // See App\Scopes\HostScope.
        static::addGlobalScope(new HostScope());
    }
}
