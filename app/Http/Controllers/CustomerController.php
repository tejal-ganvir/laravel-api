<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use Config;
use DB;
use Hash;

use App\Http\Traits\SmsHelper;



class CustomerController extends Controller
{

     use SmsHelper;


   // public function index()
   //  {
   //      return Customer::all();
   //  }

    public function addCustomer(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->name) || empty($request->mobile) || empty($request->email) || empty($request->password)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $oldUser = Customer::where('mobile', $request->mobile)->first();


          if ($oldUser) {

            //echo $oldUser;
            
            return response()->json([
            'data' => null,
            'status' => Config::get('appconstants.error'),
            'message' => Config::get('appconstants.mobile_already_registered')
            ]);
            
          }
          

       		 $user = new Customer();
       		 $user->name = $request->name;
       		 $user->mobile = $request->mobile;
       		 $user->email = $request->email;
			     $user->password = md5($request->password);
            $user->save();

          $user = Customer::latest()->first();
          $user->password = '$$$';



    		return response()->json([
            'data' => $user,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.registered_success')
        ]);

    		

    }

    public function updateCustomer(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $user = Customer::where('id', $request->id)->first();


          if ($user) {

           // print_r($request->all());

            $user = $user->update($request->except(['password']));

            $user = Customer::where('id', $request->id)->first();

            
            return response()->json([
            'data' => $user,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.user_update_success')
            ]);
            
          }
          else
          {
              return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.user_not_found')
              ]);
          }
          
    }

    public function login(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->mobile) || empty($request->password)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $oldUser = Customer::where('mobile', $request->mobile)->where('password', md5($request->password))->first();




          if ($oldUser) {


                if($oldUser->is_otp_verified == 'false'){
                   
                      $oldUser->password = '$$$';
                    return response()->json([
                    'data' => null,
                    'status' => Config::get('appconstants.error'),
                    'status_code' => Config::get('appconstants.otp_not_verified'),
                    'message' => Config::get('appconstants.otp_not_verified_error')
                    ]);

                }
                else{

                    $oldUser->password = '$$$';
                  return response()->json([
                  'data' => $oldUser,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.login_success')
                  ]);

                }

          }
          else
          {
               return response()->json([
                'data' => null,
                'status' => Config::get('appconstants.error'),
                'status_code' => Config::get('appconstants.code_invalid_credentials'),
                'message' => Config::get('appconstants.invalid_credentials')
                ]);
          }
          

    }


    public function forgotPassword(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->mobile)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $oldUser = Customer::where('mobile', $request->mobile)->first();




          if ($oldUser) {

                $this->resendOTPInternal($request->mobile);


                  return response()->json([
                  'data' => $oldUser,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.forgot_password_success')
                  ]);

          }
          else
          {
               return response()->json([
                'data' => null,
                'status' => Config::get('appconstants.error'),
                'message' => Config::get('appconstants.user_not_found')
                ]);
          }
          

    }


    public function getProfile(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->user_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $oldUser = DB::table('customers as C')->where('id', $request->user_id)->select('C.*','sm.name as state_name','cm.name as city_name')
          ->join('states_master as sm', 'C.state_id', '=', 'sm.state_id','left')
          ->join('city_master as cm', 'C.city_id', '=', 'cm.city_id','left')
          ->first();

          if ($oldUser) {

                  $oldUser->password = '$$$';
                  return response()->json([
                  'data' => $oldUser,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.user_profile_success')
                  ]);

          }
          else
          {
               return response()->json([
                'data' => null,
                'status' => Config::get('appconstants.error'),
                'message' => Config::get('appconstants.user_not_found')
                ]);
          }
          

    }

    public function changePassword(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->user_id)  || empty($request->old_password)|| empty($request->new_password) ) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $user = Customer::where('id', $request->user_id)->where('password', md5($request->old_password))->first();


          if ($user) {

           // print_r($request->all());

            $user = Customer::find($request->user_id)->update(['password' => md5($request->new_password)]);
            
            return response()->json([
            'data' => null,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.password_changed_success')
            ]);
            
          }
          else
          {
              return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.password_mismatched_error')
              ]);
          }
          
    }


    public function changePasswordAfterForgot(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->new_password) || empty($request->user_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $user = Customer::where('id', $request->user_id)->first();


          if ($user) {

           // print_r($request->all());

            $user = Customer::find($request->user_id)->update(['password' => md5($request->new_password)]);
            
            return response()->json([
            'data' => null,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.password_changed_success')
            ]);
            
          }
          else
          {
              return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.user_not_found')
              ]);
          }
          
    }


    public function changeMobileNo(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->new_mobile) || empty($request->user_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


          $oldUser = Customer::where('mobile', $request->new_mobile)->first();

          if (empty($oldUser)) {

                $this->resendOTPInternal($request->new_mobile);


                  return response()->json([
                  'data' => $oldUser,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.change_mobile_success')
                  ]);

          }
          else
          {
               return response()->json([
                'data' => null,
                'status' => Config::get('appconstants.error'),
                'message' => Config::get('appconstants.user_exist_error')
                ]);
          }
          

    }


    public function searchCustomer(Request $request)
    {
      // Check parameter missing condition
        if (empty($request->search_customer) && empty($request->service_centre_id) ) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $user=DB::table('customer_service_request as csr')
            ->select( 
                'csr.service_centre_id as service_centre_id',
                'cu.name as customer_name',
                'cu.id as customer_id',


                //'services.name as service_type_name'

            )
            ->join(
                'customers as cu',
                'cu.id','=','csr.customer_id'
            )
            // ->join(
            //     'services',
            //     'services.id','=','csr.service_type_id'
            // )
            ->where('service_centre_id', $request->service_centre_id)
            ->where('cu.name', 'like',  $request->search_customer . '%')
            
            ->orderBy('csr.id','desc')

            ->distinct()->get();

        if ($user) {

                  
                  return response()->json([
                  'data' => $user,
                  'status' => Config::get('appconstants.success'),
                  'message' => ''
                  ]);

          }
          else
          {
               return response()->json([
                'data' => null,
                'status' => Config::get('appconstants.error'),
                'message' => ''
                ]);
          }


    }

}
