<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AllergyItemCampaign extends Pivot
{

    protected $casts = [
        'id'=>'integer',
        'item_campaign_id'=>'integer',
        'nutrition_id'=>'integer'
    ];

}
