<?php

namespace App\Models;

use App\Traits\MasksSensitiveAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory , MasksSensitiveAttributes;

    protected $fillable = ['ip_address'];

    public function orders()
    {
        return $this->hasMany(Order::class,'user_id','id');
    }
}

