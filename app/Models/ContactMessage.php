<?php

namespace App\Models;

use App\Traits\MasksSensitiveAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory ,MasksSensitiveAttributes;
    protected $guarded = ['id'];
    protected $casts = [
        'status' => 'integer',
    ];

}
