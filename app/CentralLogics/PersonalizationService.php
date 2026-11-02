<?php

namespace App\CentralLogics;

/**
 * Host proxy for the AI module's PersonalizationService.
 *
 * Delegates all calls to Modules\AI when the module is present AND the admin
 * `customer_personalization_status` toggle is on. Returns unchanged
 * queries/collections when disabled, so the catalog logic call-sites are safe
 * to leave in permanently (no-op when off / module absent).
 *
 * StackFood is single-module (food) — no $moduleId parameters.
 */
class PersonalizationService
{
    private static function service(): ?string
    {
        if (!class_exists(\Modules\AI\app\Services\Personalization\PersonalizationService::class)) return null;
        if (!class_exists(\Modules\AI\app\Core\AiModule::class)) return null;
        if (!\Modules\AI\app\Core\AiModule::isPersonalizationActive()) return null;
        return \Modules\AI\app\Services\Personalization\PersonalizationService::class;
    }

    // --- Recording methods (write to preferences) ---

    public static function recordItemAction(int $userId, int $itemId, string $signal): void
    {
        $svc = self::service();
        if ($svc) $svc::recordItemAction($userId, $itemId, $signal);
    }

    public static function recordStoreAction(int $userId, int $storeId, string $signal): void
    {
        $svc = self::service();
        if ($svc) $svc::recordStoreAction($userId, $storeId, $signal);
    }

    public static function recordSearchAction(int $userId, string $keyword): void
    {
        $svc = self::service();
        if ($svc) $svc::recordSearchAction($userId, $keyword);
    }

    public static function markSummaryDirty(int $userId): void
    {
        $svc = self::service();
        if ($svc) $svc::markSummaryDirty($userId);
    }

    // --- Apply methods (modify queries) ---

    public static function applyItemPersonalization($query, ?int $userId, $filter = null)
    {
        $svc = self::service();
        return $svc ? $svc::applyItemPersonalization($query, $userId, $filter) : $query;
    }

    public static function applyStorePersonalization($query, ?int $userId, $filter = null)
    {
        $svc = self::service();
        return $svc ? $svc::applyStorePersonalization($query, $userId, $filter) : $query;
    }

    public static function applyCategoryPersonalization($query, ?int $userId)
    {
        $svc = self::service();
        return $svc ? $svc::applyCategoryPersonalization($query, $userId) : $query;
    }

    public static function applyCampaignPersonalization($query, ?int $userId)
    {
        $svc = self::service();
        return $svc ? $svc::applyCampaignPersonalization($query, $userId) : $query;
    }

    public static function reorderByPreference($collection, ?int $userId, string $matchField, string $preferenceType)
    {
        $svc = self::service();
        return $svc ? $svc::reorderByPreference($collection, $userId, $matchField, $preferenceType) : $collection;
    }

    // --- Rebuild (called from job/command) ---

    public static function rebuildSummary(int $userId): void
    {
        $svc = self::service();
        if ($svc) $svc::rebuildSummary($userId);
    }

    public const REBUILD_THRESHOLD = 5;
}
