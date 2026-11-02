<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Surge price moved off the zone permission onto its own. Every role that could reach the
 * screen yesterday held 'zone', so carry that grant across - otherwise the release silently
 * revokes access and each role has to be re-ticked by hand.
 *
 * New roles start without it: the split only exists so it can be withheld from here on.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->shiftModules(fn (array $modules) => in_array('zone', $modules, true) && ! in_array('surge_price', $modules, true)
            ? [...$modules, 'surge_price']
            : null);
    }

    public function down(): void
    {
        $this->shiftModules(fn (array $modules) => in_array('surge_price', $modules, true)
            ? array_values(array_diff($modules, ['surge_price']))
            : null);
    }

    /**
     * modules is a JSON string column rather than a relation, so each row is decoded,
     * rewritten by the callback and written back. A callback returning null leaves the row
     * untouched, which also covers the roles whose column is null or unparseable.
     */
    private function shiftModules(callable $rewrite): void
    {
        DB::table('admin_roles')->select('id', 'modules')->orderBy('id')->chunk(200, function ($roles) use ($rewrite) {
            foreach ($roles as $role) {
                $modules = json_decode($role->modules ?? '', true);

                if (! is_array($modules)) {
                    continue;
                }

                $updated = $rewrite($modules);

                if ($updated === null) {
                    continue;
                }

                DB::table('admin_roles')->where('id', $role->id)->update(['modules' => json_encode($updated)]);
            }
        });
    }
};
