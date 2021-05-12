<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Otp;
use App\Customer;

use App\AdminUser;
use App\ServiceCentreModel;
use App\Http\Traits\SmsHelper;

use DB;

use Config;


class AdminController extends Controller
{
  use SmsHelper;


    
    public function addUser(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->name) || empty($request->mobile) ) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


        //Check if user already exist
        $old_user = AdminUser::where('mobile',$request->mobile)->first();

        if ($old_user) {
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.user_exist_error')
            ]);
        }



        $random_password = mt_rand(10000000, 99999900);


        $user           = new AdminUser();
        $user->name     = $request->name;
        $user->mobile   = $request->mobile;
        $user->password = md5($random_password);
        $user->save();


        $model = AdminUser::latest('updated_at')->first();


        //Send SMS
        //$message = 'Your credentials for Narsinhakrupa Admin App are:' 
        $message = 'Your credentials for Narsinhakrupa Admin App are: Username: '.$user->mobile.' ,Password: '.$random_password;
        $this->sendSmsMsg($request->mobile,$message);


      
         
          return response()->json([
              'data' => $model,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.admin_user_create_success')
            ]);
         
    }


    public function adminLogin(Request $request)
    {


        // Check parameter missing condition
        if ( empty($request->mobile)  || empty($request->password)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


        //Check if user already exist
        $old_user = AdminUser::where('mobile',$request->mobile)->where('password',md5($request->password))->first();


        if (empty($old_user)) {
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.invalid_credentials')
            ]);
        }



        if ($old_user->isActive == 'false') {
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.user_not_active_error')
            ]);
        }

      
         
          return response()->json([
              'data' => $old_user,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.login_success')
            ]);
         
    }

   

    /**
    * Delete vehicle
    */
    public function updateStatus(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->admin_id) || empty($request->status)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $result = AdminUser::where('id',$request->admin_id)->update(['isActive'=>$request->status]);
          

          $result = AdminUser::where('id',$request->admin_id)->first();

        

              return response()->json([
                  'data' => $result,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.user_update_success')
                ]);
          

    }


    public function updateMobileNo(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->admin_id) || empty($request->mobile)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


        $other_user = AdminUser::where('id','<>',$request->admin_id)->where('mobile',$request->mobile)->first();

        if ($other_user) {
          
          return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.admin_with_same_mobile_exist_error')
            ]);
        }



          $result = AdminUser::where('id',$request->admin_id)->update(['mobile'=>$request->mobile]);
          

          $result = AdminUser::where('id',$request->admin_id)->first();

        

              return response()->json([
                  'data' => $result,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.user_update_success')
                ]);
          

    }

    public function getAdminUserList(Request $request)
    {

    
  
        // $allData = AdminUser::select(
        //         'admin_master.*',
        //         'sc.name as service_centre_name'

        //     )->leftJoin(
        //         'service_centres as sc',
        //         'sc.admin_id','=','admin_master.id'
        //     )
        //     ->where('role','ADMIN')->get();



        // $allData = DB::table('admin_master as am')
        //     ->select(
        //         'am.*',
        //         'sc.name as service_centre_name'

        //     )
        //     ->join(
        //         'service_centres as sc',
        //         'sc.admin_id','=','am.id'
        //     )
           
        //     ->get();


        $allData = AdminUser::get();

        $finalArray = array();

        foreach ($allData  as $model) {

          $sercenter = ServiceCentreModel::where('admin_id',$model->id)->first();

           // $model->service_centre_name = $sercenter->name;

            if($sercenter){
                 $model->service_centre_name = $sercenter->name;
                 $model->service_centre_id = $sercenter->id;
            }
            else{
                $model->service_centre_name = null;
                $model->service_centre_id = null;

            }

              array_push($finalArray, $model);
         
        }


        if($allData){
              return response()->json([
                   'data' =>  $finalArray,
                  'status' => Config::get('appconstants.success'),
                  'message' => ''
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => ''
              ]);
        }

    }  


    public function getAdminStatus(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->admin_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }
    
        $result = AdminUser::select('isActive')->where('id',$request->admin_id)->first();
        //$status = $result->isActive;

        if(!empty($result)){
              return response()->json([
                   'data' =>  $result,
                  'status' => Config::get('appconstants.success'),
                  'message' => ''
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.error'),
                  'message' => ''
              ]);
        }

    }  


    public function changePassword(Request $request)
    {


        // Check parameter missing condition
        if ( empty($request->user_id)  || empty($request->current_password) || empty($request->new_password)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


        //Check if user already exist
        $old_user = AdminUser::where('id',$request->user_id)->where('password',md5($request->current_password))->first();



        if (empty($old_user)) {
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.invalid_credentials')
            ]);
        }


        AdminUser::where('id',$request->user_id)->update(['password'=>md5($request->new_password)]);


         
          return response()->json([
              'data' => $old_user,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.password_update_success')
            ]);
         
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


          $oldUser = AdminUser::where('mobile', $request->mobile)->first();




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


          $user = AdminUser::where('id', $request->user_id)->first();


          if ($user) {

           // print_r($request->all());

            $user = AdminUser::find($request->user_id)->update(['password' => md5($request->new_password)]);
            
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


}
