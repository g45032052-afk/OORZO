<?php

namespace App\Traits;

use App\CentralLogics\SurgePriceLogic;

/**
 * Instance-side sugar over {@see SurgePriceLogic} for classes that resolve
 * surge pricing with `$this->`.
 *
 * All logic lives in SurgePriceLogic because two of the three delivery-fee
 * engines are static methods and cannot reach a trait through `$this`.
 */
trait HasSurgePrice
{
    public function getSurgePriceValue($zoneId, $datetime = null): array
    {
        return SurgePriceLogic::resolve($zoneId, $datetime);
    }

    public function applySurgeToFee(float $fee, array $surge): float
    {
        return SurgePriceLogic::applyToFee($fee, $surge);
    }

    public function surgeCustomerNote(array $surge): ?string
    {
        return SurgePriceLogic::customerNote($surge);
    }
}
