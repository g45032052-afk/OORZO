<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEditLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'order_id' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function getCreatedAtAttribute(){
        return date('Y-m-d H:i:s',strtotime($this->attributes['created_at']));
    }
}
