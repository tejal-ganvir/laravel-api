<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




//Customer module 
Route::get('users', 'CustomerController@index');
Route::post('v1/customer/register', 'CustomerController@addCustomer');
Route::post('v1/customer/login', 'CustomerController@login');
Route::post('v1/customer/update', 'CustomerController@updateCustomer');
Route::post('v1/customer/user_profile', 'CustomerController@getProfile');
Route::post('v1/customer/change_password', 'CustomerController@changePassword');
Route::post('v1/customer/forgot_password', 'CustomerController@forgotPassword');
Route::post('v1/customer/change_password_forgot', 'CustomerController@changePasswordAfterForgot');
Route::post('v1/customer/change_mobile', 'CustomerController@changeMobileNo');
Route::post('v1/customer/search_customer', 'CustomerController@searchCustomer');


//Utils methods
Route::post('v1/utils/resend_otp', 'UtilController@resendOTP');
Route::post('v1/utils/verify_otp', 'UtilController@verifyOTP');
Route::post('v1/utils/state_list', 'UtilController@getStates');
Route::post('v1/utils/city_list', 'UtilController@getCities');
Route::post('v1/utils/send_sms', 'UtilController@sendSms');


//Services
Route::post('v1/services/get_types', 'ServicesController@getServiceTypes');
Route::post('v1/services/place_service_request', 'ServicesController@addServiceRequest');
Route::post('v1/services/my_service_history', 'ServicesController@getMyServiceHisotory');
Route::post('v1/services/service_requests', 'ServicesController@getCustomerServiceRequests');
Route::post('v1/services/search_service_requests', 'ServicesController@searchCustomerServiceRequests');
Route::post('v1/services/add_service_type', 'ServicesController@addServiceType');
Route::post('v1/services/update_service_req_status', 'ServicesController@updateServiceStatus');
Route::post('v1/services/update_customer_service_req_status', 'ServicesController@updateCustomerServiceStatus');
Route::post('v1/services/update_bill_amount', 'ServicesController@updateBillAmount');
Route::post('v1/services/send_completion_notification', 'ServicesController@sendServiceCompletionReminder');
Route::post('v1/services/get_counts', 'ServicesController@getCountsRequests');
Route::post('v1/services/update_status', 'ServicesController@updateServiceTypeStatus');



//Feedback
Route::post('v1/feedback/add_feedback', 'UtilController@addFeedback');
Route::post('v1/feedback/get_feedbacks', 'UtilController@getCustomerFeedback');

//Vehicle
Route::post('v1/vehicle/add_vehicle', 'VehicleController@addVehicle');
Route::post('v1/vehicle/delete_vehicle', 'VehicleController@deleteVehicle');
Route::post('v1/vehicle/update_vehicle', 'VehicleController@updateVehicle');
Route::post('v1/vehicle/get_user_vehicles', 'VehicleController@getVehicleList');
Route::post('v1/vehicle/test', 'VehicleController@test');
Route::post('v1/vehicle/test1', 'VehicleController@test1');



//Service Centre
Route::post('v1/service_centre/add_service_centre', 'ServiceCentreController@addServiceCenter');
Route::post('v1/service_centre/update_service_centre', 'ServiceCentreController@updateServiceCenter');
Route::post('v1/service_centre/delete_service_centre', 'ServiceCentreController@deleteServiceCenter');
Route::post('v1/service_centre/service_centres', 'ServiceCentreController@getServiceCenterList');
Route::post('v1/service_centre/get_customer_list', 'ServicesController@getCustomerListForServiceCenter');


//Admin Users
Route::post('v1/admin/create_user', 'AdminController@addUser');
Route::post('v1/admin/admin_users', 'AdminController@getAdminUserList');
Route::post('v1/admin/update_status', 'AdminController@updateStatus');
Route::post('v1/admin/get_status', 'AdminController@getAdminStatus');
Route::post('v1/admin/login', 'AdminController@adminLogin');
Route::post('v1/admin/change_password', 'AdminController@changePassword');
Route::post('v1/admin/change_mobile', 'AdminController@updateMobileNo');
Route::post('v1/admin/forgot_password', 'AdminController@forgotPassword');
Route::post('v1/admin/change_password_forgot', 'AdminController@changePasswordAfterForgot');


//Route::post('v1/admin/change_password_forgot', 'AdminController@changePasswordAfterForgot');

