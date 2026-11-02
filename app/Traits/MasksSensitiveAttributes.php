<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait MasksSensitiveAttributes
{
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->shouldMask() ? $this->maskEmail($value) : $value,
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->shouldMask() ? $this->maskPhone($value) : $value,
        );
    }
    protected function contactPersonNumber(): Attribute
    {

        return Attribute::make(
            get: fn ($value) => $this->shouldMask() ? $this->maskPhone($value) : $value,
        );
    }

    protected function shouldMask(): bool
    {
        return getEnvMode() === 'demo' && !request()->is('api/*');

    }

    private function maskEmail($email)
    {
        if (!$email || !str_contains($email, '@')) {
            return $email;
        }

        [$name, $domain] = explode('@', $email);

        return substr($name, 0, 1)
            . str_repeat('*', max(1, min(10, strlen($name) - 1)))
            . '@' . $domain;
    }

    private function maskPhone($phone)
    {
        if (!$phone || strlen($phone) < 6) {
            return $phone;
        }

        return substr($phone, 0, 3)
            . str_repeat('*', max(4, min(10, strlen($phone) - 1)));
    }
}
