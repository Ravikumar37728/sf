<?php

use App\Http\Controllers\API\CallDetailAPIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function (Router $router) {
    return collect($router->getRoutes()->getRoutesByMethod()["GET"])->map(function ($value, $key) {
        return url($key);
    })->values();
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::namespace('App\Http\Controllers\API')->group(function () {

    Route::apiResource('cities', 'CitiesAPIController')->only(['index', 'show']);

    // Super Admin
    Route::prefix('super-admin')->group(function () {
        Route::middleware(['check.permission'])->group(function () {
            Route::post('login', 'LoginAPIController@login')->name('super.admin.login');
            Route::middleware(['auth:api'])->group(function () {
                Route::get('logout', 'LoginAPIController@logout')->name('super.admin.logout');
                Route::post('change-password', 'LoginAPIController@changePassword')->name('super.admin.change-password');
            });
        });
    });

    Route::post('login', 'LoginAPIController@login')->name('user.login');
    Route::middleware(['auth:api', 'check.permission'])->group(function () {
        Route::get('logout', 'LoginAPIController@logout')->name('user.logout');
        Route::post('change-password', 'LoginAPIController@changePassword')->name('user.change-password');

        // Admin
        Route::prefix('admin')->group(function () {
            Route::post('delete-multiple', 'AdminAPIController@deleteMultiple')->name('admin.delete.multiple');
            Route::post('update-remark/{admin}', 'AdminAPIController@updateRemark')->name('admin.update.remark');
            Route::post('update-status/{admin}', 'AdminAPIController@updateStatus')->name('admin.update.status');
            Route::post('update/{admin}', 'AdminAPIController@update')->name('admin.update');
            Route::apiResource('admin', 'AdminAPIController', [
                'names' => [
                    'index' => 'admin.index',
                    'store' => 'admin.store',
                    'show' => 'admin.show',
                    'update' => 'admin.update',
                    'destroy' => 'admin.destroy'
                ]
            ])->except(['update']);
        });

        // Call details
        Route::get('my-calls', 'CallDetailAPIController@myCalls')->name('admin.mycalls');
        Route::get('calls/{id}', 'CallDetailAPIController@calls')->name('show.calls');
        Route::get('sub-admin-calls/{sub_admin}', 'CallDetailAPIController@subAdminCalls')->name('sub.admin.calls');
        Route::get('admin-calls/{admin}', 'CallDetailAPIController@adminCalls')->name('admin.calls');
        Route::get('leadmanager-calls/{lead_manager}', 'CallDetailAPIController@leadmnagercalls')->name('lead.manager.calls');
        Route::post('update/call-detail/{call_detail}', 'CallDetailAPIController@update')->name('call.detail.update');
        Route::apiResource('call-detail', 'CallDetailAPIController', [
            'names' => [
                'index' => 'call.detail.index',
                'store' => 'call.detail.store',
                'show' => 'call.detail.show',
                'update' => 'call.detail.update',
                'destroy' => 'call.detail.destroy'
            ]
        ])->except(['update', 'delete']);

        // Sub Admin
        Route::prefix('sub-admin')->group(function () {
            Route::get('my-subadmins', 'SubAdminAPIController@mySubAdmin')->name('my.subadmin');
            Route::get('admin-wise-subadmin/{admin}', 'SubAdminAPIController@adminWiseSubadmin')->name('admin.subadmin');
            Route::post('delete-multiple', 'SubAdminAPIController@deleteMultiple')->name('subadmin.delete.multiple');
            Route::post('update-status/{sub_admin}', 'SubAdminAPIController@updateStatus')->name('subadmin.update.status');
            Route::post('update/{sub_admin}', 'SubAdminAPIController@update')->name('subadmin.update');
            Route::apiResource('sub-admin', 'SubAdminAPIController', [
                'names' => [
                    'index' => 'sub.admin.index',
                    'store' => 'sub.admin.store',
                    'show' => 'sub.admin.show',
                    'update' => 'sub.admin.update',
                    'destroy' => 'sub.admin.destroy'
                ]
            ])->except(['update']);
        });

        // Lead Managers
        Route::prefix('lead-manager')->group(function () {
            Route::post('store-salesassociate', 'LeadManagerAPIController@storesalesassociate')->name('add.salesassociate');
            Route::get('my-lead-manager', 'LeadManagerAPIController@myLeadManager')->name('my.leadmanager');
            Route::get('admin-wise-lm/{admin}', 'LeadManagerAPIController@adminWiseLM')->name('admin.leadmanager');
            Route::get('subadmin-wise-lm/{sub_admin}', 'LeadManagerAPIController@subadminWiseLM')->name('subadmin.leadmanager');
            Route::post('delete-multiple', 'LeadManagerAPIController@deleteMultiple')->name('lead.manager.delete.multiple');
            Route::post('update-status/{lead_manager}', 'LeadManagerAPIController@updateStatus')->name('lead.manager.update.status');
            Route::post('update/{lead_manager}', 'LeadManagerAPIController@update')->name('lead.manager.update');
            Route::apiResource('lead-manager', 'LeadManagerAPIController', [
                'names' => [
                    'index' => 'lead.manager.index',
                    'store' => 'lead.manager.store',
                    'show' => 'lead.manager.show',
                    'update' => 'lead.manager.update',
                    'destroy' => 'lead.manager.destroy'
                ]
            ])->except(['update']);
        });

        // Sales Associates
        Route::prefix('sa')->group(function () {
            Route::get('my-sales-associates', 'SalesAssociateAPIController@mySalesAssociates')->name('my.salesassociates');
            Route::get('admin-wise-sa/{admin}', 'SalesAssociateAPIController@adminWiseSA')->name('admin.salesassociates');
            Route::get('subadmin-wise-sa/{sub_admin}', 'SalesAssociateAPIController@subAdminWiseSA')->name('subadmin.salesassociates');
            Route::get('leadmanager-wise-sa/{lead_manager}', 'SalesAssociateAPIController@leadManagerWiseSA')->name('leadmanager.salesassociates');
            Route::post('delete-multiple', 'SalesAssociateAPIController@deleteMultiple')->name('sales.associate.delete.multiple');
            Route::post('update-status/{sales_associate}', 'SalesAssociateAPIController@updateStatus')->name('sales.associate.update.status');
            Route::post('update/{sales_associate}', 'SalesAssociateAPIController@update')->name('sales.associate.update');
            Route::apiResource('sales-associate', 'SalesAssociateAPIController', [
                'names' => [
                    'index' => 'sales.associate.index',
                    'store' => 'sales.associate.store',
                    'show' => 'sales.associate.show',
                    'update' => 'sales.associate.update',
                    'destroy' => 'sales.associate.destroy'
                ]
            ])->except(['update']);
        });

        // Visits
        Route::prefix('visits')->group(function () {
            Route::get('my-visits', 'VisitAPIController@myVisits')->name('my.visits');
            Route::get('visits/{id}', 'VisitAPIController@visits')->name('show.visits');

            Route::get('sales-associates-wise-visits/{sales_associate}', 'VisitAPIController@saWiseVisits')->name('salesassociates.visits');
            Route::post('update/{visit}', 'VisitAPIController@update')->name('visit.update');
            Route::apiResource('visit', 'VisitAPIController', [
                'names' => [
                    'index' => 'visit.index',
                    'store' => 'visit.store',
                    'show' => 'visit.show',
                    'update' => 'visit.update',
                    'destroy' => 'visit.destroy'
                ]
            ])->except(['update', 'destroy']);
        });

        // Time Log
        Route::post('in-time', 'TimeLogAPIController@inTimeLog')->name('user.in.time');
        Route::post('check-out-time', 'TimeLogAPIController@checkOutTime')->name('user.check.outtime');
        Route::post('out-time', 'TimeLogAPIController@outTimeLog')->name('user.out.time');
        Route::get('my-time-log', 'TimeLogAPIController@myTimeLog')->name('my.timelog');
        Route::get('admin-time-log', 'TimeLogAPIController@adminTimeLog')->name('admin.timelog');
        Route::get('admin-time-log/{admin}', 'TimeLogAPIController@singleAdminTimeLog')->name('single.admin.timelog');
        Route::get('subadmin-time-log/{sub_admin}', 'TimeLogAPIController@singleSubAdminTimeLog')->name('single.subadmin.timelog');
        Route::get('leadmanager-time-log/{lead_manager}', 'TimeLogAPIController@singleLeadManagerTimeLog')->name('single.leadmanager.timelog');
        Route::get('salesassociates-time-log/{sales_associate}', 'TimeLogAPIController@singleSalesAssociatesTimeLog')->name('sales.associates.timelog');
        Route::get('admin-team/{admin}', 'TimeLogAPIController@adminTeam')->name('admin.team');

        // Excel Import
        Route::post('excel-import/visits', 'VisitAPIController@importExcelVisit')->name('excel.import.visit');
        Route::post('excel-import/calls', 'CallDetailAPIController@importExcelCall')->name('excel.import.call');
    });
});

Route::any('{any}', function () {
    return response()->json([
        'status' => false,
        'status_code' => config('constants.validation_codes.not_found'), 'message' => config('constants.messages.errors.not_found')
    ], config('constants.validation_codes.not_found'));
})->where('any', '.*')->name('not-found');
