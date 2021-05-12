<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service;

use App\ServiceRequest;
use App\Customer;
use App\ServiceCentreModel;

use Config;
use DB;

use App\Http\Traits\SmsHelper;

class ServicesController extends Controller
{
   use SmsHelper;

    public function getServiceTypes(Request $request)
    {


      if (empty($request->service_centre_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

         if (!empty($request->status)) {

              $allData = Service::where('service_centre_id',$request->service_centre_id)->where('isActive',$request->status)->get();

         }
         else
         {
              $allData = Service::where('service_centre_id',$request->service_centre_id)->get();

         }


    		return response()->json([
            'data' => $allData,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.get_service_type_success')
        ]);

    }

    public function addServiceType(Request $request)
    {

      if (empty($request->service_centre_id) || empty($request->name) || empty($request->service_description)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        Service::create($request->all());
        $model = Service::latest()->first();



        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_type_added_success')
        ]);

    }


    public function updateServiceTypeStatus(Request $request)
    {

      if (empty($request->service_type_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        //Service::create($request->all());

        Service::where('id',$request->service_type_id)->update($request->except(['service_type_id']));
          
        $model = Service::where('id',$request->service_type_id)->first();


       // $model = Service::latest()->first();


        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.user_update_success')
        ]);

    }


    public function addServiceRequest(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->service_centre_id) || empty($request->customer_id)|| empty($request->date) || empty($request->current_meter_reading) || empty($request->service_type_id) || empty($request->vehicle_no) || empty($request->problem_description)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


         ServiceRequest::create($request->all());

       //  $model = ServiceRequest::latest()->first();


         $model = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->latest()->first();


      //Send sms to customer

          $user_model = Customer::where('id',$request->customer_id)->first();
          $service_type_model = Service::where('id',$request->service_type_id)->first();


          if(!empty($user_model->mobile))
          {
              //$message = 'Thanks, your request for '.$service_type_model->name.' of your '.$request->vehicle_no.' has been placed successfully. Our team will take action shortly';

            $message =  'Thanks, your request for '.$service_type_model->name.' has been placed successfully, booking id of your vehicle no. '.$request->vehicle_no.' is '.$model->id.'. Our team will take action shortly';

              $this->sendSmsMsg($user_model->mobile,$message);

          }


        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_request_placed_success')
        ]);

    }

    public function updateServiceStatus(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->service_req_id)|| empty($request->status)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


         ServiceRequest::where('id',$request->service_req_id)->update(['status'=>$request->status]);

         //$model = ServiceRequest::latest()->first();


         $model = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->where('csr.id',$request->service_req_id)
            ->first();

             


            //Send sms on status update
            $user_model = Customer::where('id',$model->customer_id)->first();
            $service_type_model = Service::where('id',$model->service_type_id)->first();


            if ($request->status == 'Confirmed') {
             

                $message = 'Your service request for '.$service_type_model->name.' dated on '.$model->date.' has been confirmed';

                $this->sendSmsMsg($user_model->mobile,$message);

            }
            else if ($request->status == 'Rejected') {
             

                $message = 'Your service request for '.$service_type_model->name.' dated on '.$model->date.' has been rejected, please try for next date. Sorry for your inconvenience';

                $this->sendSmsMsg($user_model->mobile,$message);

            }
            else if ($request->status == 'AlmostCompleted') {
             

                $message = 'Your Vehicle '.$model->vehicle_no.' will be ready in 15 min, Bill for service is Rs. <amount>';

                $this->sendSmsMsg($user_model->mobile,$message);

            }
            else if ($request->status == 'Completed') {
             

                $message = 'Thank you for choosing our service. Please provide your feedback';

                $this->sendSmsMsg($user_model->mobile,$message);

            }
            else if ($request->status == 'Cancelled') {
             

                //$message = 'Thank you for choosing our service. Please provide your feedback';

                $message = 'Your vehicle service request dated on '.$model->date.' has been cancelled by '.$model->service_centre_name.', For further inquiry please contact service center';

                $this->sendSmsMsg($user_model->mobile,$message);

            }
            

            
        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_request_updated_success')
        ]);

    }

    public function updateCustomerServiceStatus(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->service_req_id)|| empty($request->status)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


         ServiceRequest::where('id',$request->service_req_id)->update(['status'=>$request->status]);

         //$model = ServiceRequest::latest()->first();


         $model = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->where('csr.id',$request->service_req_id)
            ->first();

             


            //Send sms on status update
            $user_model = Customer::where('id',$model->customer_id)->first();
            $service_type_model = Service::where('id',$model->service_type_id)->first();
            $service_center_model = ServiceCentreModel::where('id',$model->service_centre_id)->first();


             if ($request->status == 'Cancelled') {
             

                //$message = 'Thank you for choosing our service. Please provide your feedback';
              $message = 'Booking Id '.$request->service_req_id.' of service request has been cancelled by the '.$user_model->name.' for vehicle number '.$model->vehicle_no;

                // $message = 'Your vehicle service request dated on '.$model->date.' has been cancelled by '.$model->service_centre_name.', For further inquiry please contact service center';

                $this->sendSmsMsg($service_center_model->contact_no,$message);

            }
            

            
        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_request_updated_success')
        ]);

    }

    public function updateBillAmount(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->service_req_id)|| empty($request->bill_amount)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


         ServiceRequest::where('id',$request->service_req_id)->update(['bill_amount'=>$request->bill_amount]);

         //$model = ServiceRequest::latest()->first();


         $model = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->where('csr.id',$request->service_req_id)
            ->first();

           

            
        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_request_updated_success')
        ]);

    }


    public function sendServiceCompletionReminder(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->service_req_id)|| empty($request->bill_amount)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }


         ServiceRequest::where('id',$request->service_req_id)->update(['bill_amount'=>$request->bill_amount]);

         //$model = ServiceRequest::latest()->first();


         $model = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->where('csr.id',$request->service_req_id)
            ->first();


            //Send sms on status update
            if($model)
            {
                $user_model = Customer::where('id',$model->customer_id)->first();
                $service_type_model = Service::where('id',$model->service_type_id)->first();


                $message = 'Your Vehicle '.$model->vehicle_no.' will be ready in 15 min, Bill for service is Rs. '.$request->bill_amount;

                $this->sendSmsMsg($user_model->mobile,$message);
          }

           

            
        return response()->json([
            'data' => $model,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.service_completion_notify_success')
        ]);

    }


    public function getMyServiceHisotory(Request $request)
    {

        // Check parameter missing condition
        if (empty($request->customer_id)) {
              
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


      //  $allData = ServiceRequest::where('customer_id',$request->customer_id)->offset($offset)->limit($limit)->get();


        $allData = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'sc.name as service_centre_name',
                'services.name as service_type_name'
            )
            ->join(
                'service_centres as sc',
                'sc.id','=','csr.service_centre_id'
            )
            ->join(
                'services',
                'services.id','=','csr.service_type_id'
            )
            ->where(['csr.customer_id' => $request->customer_id])
            ->orderBy('csr.id','desc')
            ->offset($offset)->limit($limit)->get();


        if($allData){
              return response()->json([
                   'data' =>  $allData,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_request_history_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_request_history_success')
              ]);
        }

    }  


    public function getCustomerServiceRequests(Request $request)
    {


       // Check parameter missing condition
        if (empty($request->service_centre_id)  ) {
              
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


      //  $allData = ServiceRequest::latest()->offset($offset)->limit($limit)->get();

        if(!empty($request->status) )
        {
           $status_condtion = ['csr.status'=>$request->status];
        }
        else{
          $status_condtion = [];
        }


        $allData = DB::table('customer_service_request as csr')
            ->select(
                'csr.*'
                //'sc.name as service_centre_name',
                //'services.name as service_type_name'

            )
            // ->join(
            //     'service_centres as sc',
            //     'sc.id','=','csr.service_centre_id'
            // )
            // ->join(
            //     'services',
            //     'services.id','=','csr.service_type_id'
            // )
            ->where('service_centre_id',$request->service_centre_id)
            ->where($status_condtion)
            ->orderBy('csr.id','desc')

            ->get();


            $finalArray = array();

        foreach ($allData  as $model) {

            $sercenter = ServiceCentreModel::where('id',$model->service_centre_id)->first();

            if($sercenter){
                 $model->service_centre_name = $sercenter->name;
            }
            else{
                $model->service_centre_name = null;
            }

            //service type
            $serviceType = Service::where('id',$model->service_type_id)->first();

            if($serviceType){
                 $model->service_type_name = $serviceType->name;
            }
            else{
                $model->service_type_name = null;
            }



            $user = Customer::where('id',$model->customer_id)->first();

            if($user){
                 $model->customer_name = $user->name;
            }
            else{
                $model->customer_name = null;
            }

              array_push($finalArray, $model);
         
        }


        if($allData){
              return response()->json([
                   'data' =>  $finalArray,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }

    } 



    public function searchCustomerServiceRequests(Request $request)
    {


       // Check parameter missing condition
        if (empty($request->search_feild)  ) {
              
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


      //  $allData = ServiceRequest::latest()->offset($offset)->limit($limit)->get();

        if(!empty($request->status))
        {
           $status_condtion = ['csr.status'=>$request->status];
        }
        else{
          $status_condtion = [];
        }


        if (preg_match('/[A-Za-z]/', $request->search_feild) && preg_match('/[0-9]/', $request->search_feild)) {
            $search_condtion = 'vehicle_no';
        }elseif (ctype_digit($request->search_feild)) {
            $search_condtion = 'csr.id';
        }else{
            $search_condtion = 'cu.name'; 
        }


        $allData = DB::table('customer_service_request as csr')
            ->select(
                'csr.*',
                'cu.name as customer_name',


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
            ->where('service_centre_id',$request->service_centre_id)
            ->where($search_condtion, 'like',  $request->search_feild . '%')
            
            ->orderBy('csr.id','desc')

            ->get();

        // $allData = DB::select('select csr.*,cu.name as customer_name from customer_service_request as csr INNER JOIN customers as cu ON csr.customer_id = cu.id where csr.service_centre_id= ? and (cu.name like "%?%" OR csr.vehicle_no like "%?%")'[$request->service_centre_id,$request->search_feild,$request->search_feild])->get();


            $finalArray = array();

        foreach ($allData  as $model) {

            $sercenter = ServiceCentreModel::where('id',$model->service_centre_id)->first();

            if($sercenter){
                 $model->service_centre_name = $sercenter->name;
            }
            else{
                $model->service_centre_name = null;
            }

            //service type
            $serviceType = Service::where('id',$model->service_type_id)->first();

            if($serviceType){
                 $model->service_type_name = $serviceType->name;
            }
            else{
                $model->service_type_name = null;
            }



            $user = Customer::where('id',$model->customer_id)->first();

            if($user){
                 $model->customer_name = $user->name;
            }
            else{
                $model->customer_name = null;
            }

              array_push($finalArray, $model);
         
        }


        if($allData){
              return response()->json([
                   'data' =>  $finalArray,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }

    } 




     public function getCountsRequests(Request $request)
    {


       // Check parameter missing condition
        if (empty($request->service_centre_id)  ) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        $conf_count = ServiceRequest::where('status','Confirmed')->where('service_centre_id',$request->service_centre_id)->get()->count();
        $pending_count = ServiceRequest::where('status','Pending')->where('service_centre_id',$request->service_centre_id)->get()->count();


        $data = array('confirmed'=> $conf_count,'pending'=> $pending_count);


        if($data){
              return response()->json([
                   'data' =>  $data,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }

    } 

    public function getCustomerListForServiceCenter(Request $request)
    {


       // Check parameter missing condition
        if (empty($request->service_centre_id)  ) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }
   

        $allData = ServiceRequest::
            select(
                'c.*'  ,
               // 'sm.name as state_name',
               //  'cm.name as city_name'
            )
            ->join(
                'customers as c',
                'customer_service_request.customer_id','=','c.id'
            )
            // ->join(
            //     'states_master as sm',
            //     'sm.state_id','=','c.state_id'
            // )
            // ->join(
            //     'city_master as cm',
            //     'cm.city_id','=','c.city_id'
            // )
            ->where('customer_service_request.service_centre_id',$request->service_centre_id)
            ->orderBy('c.name', 'asc')
           ->groupBy('customer_service_request.customer_id')
            ->get();


        if($allData){
              return response()->json([
                   'data' =>  $allData,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.service_requests_success')
              ]);
        }

    }  




}
