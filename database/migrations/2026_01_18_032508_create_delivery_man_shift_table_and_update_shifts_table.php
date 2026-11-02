<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Shift;

class CreateDeliveryManShiftTableAndUpdateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create pivot table
        Schema::create('delivery_man_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_man_id')->constrained('delivery_men')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Add columns to shifts table
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_full_day')->default(0)->after('status');
        });

        // 3. Create the System-defined Full Day Shift
        DB::table('shifts')->insert([
            'name' => 'Full Day',
            'start_time' => '00:00:00',
            'end_time' => '23:59:59',
            'status' => 1,
            'is_full_day' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Data Migration: Move existing shift_id to pivot table
        // 4. Data Migration: Move existing shift_id to pivot table or assign Full Day
        $fullDayShift = DB::table('shifts')->where('is_full_day', 1)->first();
        $fullDayShiftId = $fullDayShift->id;

        $dms = DB::table('delivery_men')->get();
        $pivotData = [];
        foreach ($dms as $dm) {
             $shiftIdToAssign = $fullDayShiftId; // Default to Full Day

             // Check if existing shift is valid
             if ($dm->shift_id) {
                 $shiftExists = DB::table('shifts')->where('id', $dm->shift_id)->exists();
                 if ($shiftExists) {
                     $shiftIdToAssign = $dm->shift_id;
                 }
             }

             $pivotData[] = [
                'delivery_man_id' => $dm->id,
                'shift_id' => $shiftIdToAssign,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($pivotData)) {
            // Bulk insert for performance
            // Chunking to be safe with potential huge datasets
            $chunks = array_chunk($pivotData, 100);
            foreach ($chunks as $chunk) {
                DB::table('delivery_man_shift')->insert($chunk);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_man_shift');

        // Remove the Full Day shift
        DB::table('shifts')->where('is_full_day', 1)->delete();

        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn('is_full_day');
        });
    }
}
