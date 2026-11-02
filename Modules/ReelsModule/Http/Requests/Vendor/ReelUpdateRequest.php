<?php

namespace Modules\ReelsModule\Http\Requests\Vendor;

use App\CentralLogics\Helpers;
use Modules\ReelsModule\Http\Requests\Admin\ReelUpdateRequest as AdminReelUpdateRequest;

class ReelUpdateRequest extends AdminReelUpdateRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'restaurant_id' => Helpers::get_restaurant_id(),
        ]);
    }
}
