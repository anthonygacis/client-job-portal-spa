<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataEntryAppointmentController;
use App\Http\Controllers\Api\DataEntryCreditsController;
use App\Http\Controllers\Api\DataEntryDeductionController;
use App\Http\Controllers\Api\DataEntryEmpStatusController;
use App\Http\Controllers\Api\DataEntryFundsController;
use App\Http\Controllers\Api\DataEntryInclusionController;
use App\Http\Controllers\Api\DataEntryOfficeController;
use App\Http\Controllers\Api\DataEntryPositionController;
use App\Http\Controllers\Api\DataEntrySalaryGradeController;
use App\Http\Controllers\Api\DataEntryTaxController;
use App\Http\Controllers\Api\DataTableController;
use App\Http\Controllers\Api\GeneralController;
use App\Http\Controllers\Api\LoadController;
use App\Http\Controllers\Api\PayrollManagementController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SettingsManagementController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login']);
Route::post('signup', [AuthController::class, 'store']);

Route::get('reports', [ReportsController::class, 'index'])->name('reports');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('check', [AuthController::class, 'authCheck']);
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // general usage
    Route::prefix('general')->group(function () {
        Route::get('load', [GeneralController::class, 'load']);
    });

    // loading complex data
    Route::prefix('load')->group(function () {

    });

    // managing user
    Route::prefix('user')->group(function () {
        Route::get('list', [UserController::class, 'list']);
        Route::get('details/{user}', [UserController::class, 'show']);
        Route::post('details', [UserController::class, 'update']);
        Route::put('update-role/{user}', [UserController::class, 'updateRole']);
        Route::post('upload-csv', [UserController::class, 'uploadFile']);
        Route::post('create-from-csv/{folder}', [UserController::class, 'storeFromCsv']);
        Route::delete('delete-csv', [UserController::class, 'deleteFile']);
        Route::delete('{user}', [UserController::class, 'delete']);
    });

    // searching data
    Route::prefix('search')->group(function () {
        Route::get('employees', [SearchController::class, 'employees']);
    });

    // data entry modules
    Route::prefix('data-entry')->group(function () {

    });

    // managing modules
    Route::prefix('manage')->group(function () {
        Route::prefix('settings')->group(function () {
            Route::put('update', [SettingsManagementController::class, 'update']);
        });
    });

    // datatable
    Route::prefix('datatable')->group(function () {
        Route::get('/', [DataTableController::class, 'index']);
    });
});
