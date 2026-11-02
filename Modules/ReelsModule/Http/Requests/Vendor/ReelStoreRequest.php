<?php

namespace Modules\ReelsModule\Http\Requests\Vendor;

use App\CentralLogics\Helpers;
use Modules\ReelsModule\Http\Requests\Admin\ReelStoreRequest as AdminReelStoreRequest;

class ReelStoreRequest extends AdminReelStoreRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => Helpers::get_restaurant_id(),
        ]);
    }
}
