<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Otp;
use App\Customer;

use App\VehicleModel;

use App\Http\Traits\SmsHelper;


use Config;


class VehicleController extends Controller
{

     use SmsHelper;

    


    public function test(Request $request)
    {


            $this->sendSmsMsg('9960019013','Hi Amol');
            echo 'SMS Sent';

    }

    public function test1(Request $request)
    {


            $this->sendSmsMsg('9960019013','Hi Amol test1');
            echo 'SMS Sent';

    }
    /**
    * Add vehicle
    */

    public function addVehicle(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->vehicle_no) || empty($request->customer_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $olduser = VehicleModel::where('vehicle_no', $request->vehicle_no)->first();

        if ($olduser) {

            
            return response()->json([
            'data' => null,
            'status' => Config::get('appconstants.error'),
            'message' => 'Vehical number '.$olduser->vehicle_no.' already exist'
            ]);
            
        }


        VehicleModel::create($request->all());
        $model = VehicleModel::latest()->first();



        $user_model = Customer::where('id',$request->customer_id)->first();



            if (!empty($request->insured)  && $request->insured == 'true') 
            {
                 $message =  'Insurance Details: Customer -'.$user_model->name.', '.$user_model->mobile.', Vehicle -'.$request->vehicle_no.', '.$request->model_name.', '.$request->manufacture_year.', Insurance -Yes, Policy Number -'.$request->policy_no.', Expiry Date -'.$request->policy_expiry_date;
            }
            else
            {
                   $message =  'Insurance Details: Customer -'.$user_model->name.', '.$user_model->mobile.', Vehicle -'.$request->vehicle_no.', '.$request->model_name.', '.$request->manufacture_year.', Insurance -No';
            }
             

                $this->sendSmsMsg(Config::get('appconstants.adviser_mobile'),$message);

                // echo $message;
            

      
         
          return response()->json([
              'data' => $model,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.add_vehicle_success')
            ]);
         

    }

    /**
    * Update vehicle
    */

    public function updateVehicle(Request $request)
    {


        // Check parameter missing condition
        if (/*empty($request->vehicle_no)  ||*/ empty($request->vehicle_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        

         VehicleModel::where('id',$request->vehicle_id)->update($request->except(['customer_id','vehicle_id']));
          
          $model = VehicleModel::where('id',$request->vehicle_id)->first();

          if($model){
              return response()->json([
                  'data' => $model,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.update_vehicle_success')
                ]);
          }
          else
          {
                return response()->json([
                  'data' => $model,
                  'status' => Config::get('appconstants.error'),
                  'message' => Config::get('appconstants.vehicle_not_found_error')
                ]);
          }
         

    }

    /**
    * Delete vehicle
    */
    public function deleteVehicle(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->vehicle_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $result = VehicleModel::where('id',$request->vehicle_id)->delete();
          

          if($result){

              return response()->json([
                  'data' => null,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.vehicle_deleted_success')
                ]);
          }
          else
          {
                return response()->json([
                  'data' => null,
                  'status' => Config::get('appconstants.error'),
                  'message' => Config::get('appconstants.vehicle_not_found_error')
                ]);
          }
         

    }

    public function getVehicleList(Request $request)
    {

        // Check parameter missing condition
        if ( empty($request->customer_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }
        

        //pagination logic
        $page  = 1;
        $limit = Config::get('appconstants.defualt_page_size');

        if(!empty($request->page)){
            $page = $request->page;
        }

        if(!empty($request->limit)){
            $limit = $request->limit;
        }

        $offset = ($page - 1) * $limit;


        $allData = VehicleModel::where('customer_id',$request->customer_id)->where('isActive','true')->offset($offset)->limit($limit)->get();

        if($allData){
              return response()->json([
                   'data' =>  $allData,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.get_vehicle_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.get_vehicle_success')
              ]);
        }

    }  
}
