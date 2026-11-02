<?php
use Illuminate\Support\Facades\Route;
use Modules\TaxModule\Http\Controllers\SystemTaxVatSetupController;
use Modules\TaxModule\Http\Controllers\TaxVatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'taxvat', 'as' => 'taxvat.','middleware' =>['admin']], function () {
    Route::get('get-taxvat-data', [TaxVatController::class, 'index'])->name('index');
    Route::post('add-taxvat-data', [TaxVatController::class, 'store'])->name('store');
    Route::put('update-taxvat-data/{taxVat} ', [TaxVatController::class, 'update'])->name('update');
    Route::get('update-taxvat-status/{taxVat} ', [TaxVatController::class, 'status'])->name('status');
    Route::get('export-taxvat', [TaxVatController::class, 'export'])->name('export');

    Route::get('system-taxvat', [SystemTaxVatSetupController::class, 'index'])->name('systemTaxvat');
    Route::put('system-taxvat', [SystemTaxVatSetupController::class, 'systemTaxVatStore'])->name('systemTaxVatStore');
    Route::get('system-taxvat-vendor-status', [SystemTaxVatSetupController::class, 'vendorStatus'])->name('systemTaxVatVendorStatus');

});
