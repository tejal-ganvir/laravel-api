<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Otp;
use App\Customer;

use App\ServiceCentreModel;


use DB;
use Config;


class ServiceCentreController extends Controller
{
    

    /**
    * Add Service Centre
    */

    public function addServiceCenter(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->name) || empty($request->address)  || empty($request->contact_no) || empty($request->state_id) || empty($request->city_id) || empty($request->admin_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }



        //Check if admin has already added the centre

        $old_record = ServiceCentreModel::where('admin_id',$request->admin_id)->first();
        
        if ($old_record) {
          
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.max_service_center_add_error')
            ]);
        }


        ServiceCentreModel::create($request->all());



        $model = DB::table('service_centres as sc')
            ->select(
                'sc.*',
                'sm.name as state_name',
                'cm.name as city_name'
            )
            ->join(
                'states_master as sm',
                'sm.state_id','=','sc.state_id'
            )
            ->join(
                'city_master as cm',
                'cm.city_id','=','sc.city_id'
            )
            ->latest()->first();

      //  $model = ServiceCentreModel::latest()->first();

      
         
          return response()->json([
              'data' => $model,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.service_centre_added_success')
            ]);
         
    }

    /**
    * Update vehicle
    */

    public function updateServiceCenter(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->centre_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

         ServiceCentreModel::where('id',$request->centre_id)->update($request->except(['centre_id','admin_id']));
          
          $model = ServiceCentreModel::where('id',$request->centre_id)->first();

          if($model){
              return response()->json([
                  'data' => $model,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_centre_updated_success')
                ]);
          }
          else
          {
                return response()->json([
                  'data' => $model,
                  'status' => Config::get('appconstants.error'),
                  'message' => Config::get('appconstants.centre_not_found_error')
                ]);
          }
         

    }

    /**
    * Delete vehicle
    */
    public function deleteServiceCenter(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->centre_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $result = ServiceCentreModel::where('id',$request->centre_id)->delete();
          

          if($result){

              return response()->json([
                  'data' => null,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_centre_deleted_success')
                ]);
          }
          else
          {
                return response()->json([
                  'data' => null,
                  'status' => Config::get('appconstants.error'),
                  'message' => Config::get('appconstants.centre_not_found_error')
                ]);
          }
         

    }

    public function getServiceCenterList(Request $request)
    {

       // Check parameter missing condition
        // if (empty($request->admin_id)) {
              
        //     return response()->json([
        //       'data' => null,
        //       'status' => Config::get('appconstants.error'),
        //       'message' => Config::get('appconstants.param_missing')
        //     ]);
        // }

    
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


        //$allData = ServiceCentreModel::offset($offset)->limit($limit)->get();

        $cond = [];

        if (!empty($request->admin_id)) {

            $cond = ['admin_id'=>$request->admin_id];
        }


         $allData = DB::table('service_centres as sc')
            ->select(
                'sc.*',
                'sm.name as state_name',
                'cm.name as city_name'
            )
            ->join(
                'states_master as sm',
                'sm.state_id','=','sc.state_id'
            )
            ->join(
                'city_master as cm',
                'cm.city_id','=','sc.city_id'
            )->where($cond)
            ->get();

        if($allData){
              return response()->json([
                   'data' =>  $allData,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_centre_list_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_centre_list_success')
              ]);
        }

    }  
}
