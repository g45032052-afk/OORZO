<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    protected $guarded = ['id'];

    /** Only created_at matters - a search is an event, it is never updated. */
    public $timestamps = false;
}
