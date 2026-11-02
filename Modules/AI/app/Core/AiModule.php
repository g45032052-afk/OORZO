<?php

namespace Modules\AI\app\Core;

use App\CentralLogics\Helpers;

/**
 * Admin kill-switches for the AI module's customer-facing features.
 *
 * Independent of the module's existing autofill Core/Factory — these only gate
 * the personalization data feature and the conversational chat assistant, each
 * via a business-setting toggle the admin controls.
 */
class AiModule
{
    public static function isChatActive(): bool
    {
        // AI chat requires BOTH its own feature toggle AND a configured/enabled
        // OpenAI provider. Without the provider every reply would only hit the
        // engine-error fallback, so an unconfigured provider is treated as off.
        return (bool) (Helpers::get_business_settings('ai_chat_status') ?? 0)
            && self::isOpenAiConfigured();
    }

    public static function isPersonalizationActive(): bool
    {
        return (bool) (Helpers::get_business_settings('customer_personalization_status') ?? 0);
    }

    /**
     * Whether the OpenAI provider is configured and enabled
     * (`openai_config.status == 1`).
     *
     * Gates every feature that makes a live OpenAI call: AI chat, and the
     * AI-keyword + persona enrichment step of customer personalization.
     * Behavioural personalization (top items/categories/stores by score) does
     * NOT depend on this and keeps working when the provider is off.
     */
    /**
     * Daily message ceilings. Every chat message is a paid provider call, so
     * these bound the bill rather than the traffic - the burst limiter on the
     * route handles the traffic.
     *
     * Each has a default that applies to a fresh install with nothing
     * configured, overridable by a business setting for anyone who wants a
     * different allowance.
     *
     * There is deliberately no way to switch a cap off. The platform ran with
     * no ceiling until now and the bill was the reason it changed, so a stray
     * 0 or -1 in the settings table must not quietly restore that. Anyone who
     * genuinely wants a very high allowance can type a very high number.
     */
    public const DEFAULT_DAILY_LIMIT_USER = 50;

    public const DEFAULT_DAILY_LIMIT_GUEST = 20;

    public const DEFAULT_DEMO_DAILY_LIMIT = 10;

    public static function chatDailyLimitForUser(): int
    {
        return self::limitSetting('ai_chat_daily_limit_user', self::DEFAULT_DAILY_LIMIT_USER);
    }

    public static function chatDailyLimitForGuest(): int
    {
        return self::limitSetting('ai_chat_daily_limit_guest', self::DEFAULT_DAILY_LIMIT_GUEST);
    }

    public static function chatDemoDailyLimit(): int
    {
        return self::limitSetting('ai_chat_demo_daily_limit', self::DEFAULT_DEMO_DAILY_LIMIT);
    }

    /**
     * Only a whole number of one or more is honoured. Anything else - unset,
     * empty, text, zero, negative - falls back to the default, so no single
     * bad value in the settings table can leave the endpoint uncapped.
     */
    private static function limitSetting(string $key, int $default): int
    {
        $value = Helpers::get_business_settings($key);

        return is_numeric($value) && (int) $value >= 1 ? (int) $value : $default;
    }

    public static function isOpenAiConfigured(): bool
    {
        $config = Helpers::get_business_settings('openai_config');
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        return (int) data_get($config, 'status') === 1;
    }
}
