<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who said no. Both sides can act on a rejected enrolment now - either may rework it or
 * cancel it - so requested_by no longer answers this: a resubmit flips the direction and the
 * original refusal would end up attributed to the wrong side.
 *
 * Display and reporting only; the buttons are the same whoever rejected.
 */
return new class extends Migration
{
    private array $tables = ['bogo_offer_restaurant', 'happy_hour_restaurant'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                // admin | restaurant, null while the row has never been rejected.
                $table->string('rejected_by', 10)->nullable()->after('rejection_reason');
            });

            // Existing rows: a rejected admin request was declined by the restaurant, a
            // rejected restaurant request was denied by the admin. True for every row written
            // so far, since nothing could resubmit and flip the direction before this release.
            DB::table($name)->where('status', 'rejected')->update([
                'rejected_by' => DB::raw("CASE WHEN requested_by = 'admin' THEN 'restaurant' ELSE 'admin' END"),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('rejected_by');
            });
        }
    }
};
