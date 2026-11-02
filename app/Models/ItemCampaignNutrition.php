<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemCampaignNutrition extends Pivot
{

    protected $casts = [
        'id'=>'integer',
        'item_campaign_id'=>'integer',
        'allergy_id'=>'integer'
    ];


}
