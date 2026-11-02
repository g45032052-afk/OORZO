<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;
    protected $casts = [
        'food_id' => 'integer',
        'user_id' => 'integer',
        'order_id' => 'integer',
        'rating' => 'integer',
        'restaurant_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    // protected $appends = ['user_name'];

    // public function getUserNameAttribute(){
    //     $user_name= $this->customer ?$this->customer?->f_name.' '.$this->customer?->l_name : translate('Customer Deleted');
    //     unset($this->customer);
    //     return $user_name; ;
    // }


    public function food()
    {
        return $this->belongsTo(Food::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status',1);
    }
}
