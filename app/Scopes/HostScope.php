<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Request-aware scope for identity-bearing models (`users` and the auth
 * aux tables). Three branches:
 *
 *   1. Backend operators bypass the scope entirely — they need to see
 *      every row regardless of storefront. The guard list is
 *      `config('auth.host_scope_bypass_guards')` (defaults to
 *      admin / vendor / vendor_employee).
 *
 *   2. Storefront requests (Builder middleware has bound a non-null
 *      `StorefrontContext` scope) filter to that scope. This is what lets
 *      `Auth::guard('customer')->attempt(...)` find storefront users —
 *      Laravel's EloquentUserProvider applies global scopes during
 *      credential lookup, so without this branch a storefront login would
 *      never resolve.
 *
 *   3. All other requests (host mobile API, host site, anonymous pages)
 *      default to host (0, 0) — strict host-customer isolation.
 *
 * If Builder is not installed, branch 2 never fires, so behaviour collapses
 * to "host only" — the correct single-tenant default.
 */
class HostScope implements Scope
{
    private const DEFAULT_BYPASS_GUARDS = ['admin', 'vendor', 'vendor_employee'];

    public function apply(Builder $builder, Model $model): void
    {
        // Branch 1: backend operators see everything.
        $bypassGuards = (array) \config('auth.host_scope_bypass_guards', self::DEFAULT_BYPASS_GUARDS);
        foreach ($bypassGuards as $guard) {
            if (\config("auth.guards.$guard") && Auth::guard($guard)->check()) {
                return;
            }
        }

        // Branch 2: storefront request — filter to the current storefront's scope.
        // Class name MUST NOT have a leading backslash — Laravel registers
        // container bindings under the bare FQN and app()->bound() string-matches.
        [$tenantId, $subTenantId] = [0, 0];
        $contextClass = 'Modules\\Builder\\Services\\StorefrontContext';
        if (\class_exists($contextClass) && app()->bound($contextClass)) {
            $scope = app($contextClass)->getScope();
            if ($scope !== null) {
                $tenantId    = (int) ($scope->tenantId ?? 0);
                $subTenantId = (int) ($scope->subTenantId ?? 0);
            }
        }

        // Branch 3: default host filter (or storefront filter from branch 2).
        $table = $model->getTable();
        $builder->where("$table.tenant_id", $tenantId)
                ->where("$table.sub_tenant_id", $subTenantId);
    }
}
