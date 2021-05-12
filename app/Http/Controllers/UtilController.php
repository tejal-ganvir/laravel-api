<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Otp;
use App\Customer;

use App\StateModel;
use App\CityModel;
use App\FeedbackModel;

use App\Http\Traits\SmsHelper;



use Config;


use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Exception;


class UtilController extends Controller
{


      use SmsHelper;


    public function resendOTP(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->mobile)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        	Otp::where('mobile',$request->mobile)->delete();

			     $six_digit_random_number = mt_rand(100000, 999999);

        	$otprow = new Otp();
       		$otprow->otp = $six_digit_random_number;
       		$otprow->mobile = $request->mobile;
            $otprow->save();

          	$otprow = Otp::latest()->first();


            //$message = "Your OTP: ".$six_digit_random_number;

            $message = $six_digit_random_number.' is your One Time Password (OTP) to verify your Mobile Number for Narsinhakrupa Auto App';

            $this->sendSmsMsg($request->mobile,$message);




    	     return response()->json([
                'data' => $otprow,
                'status' => Config::get('appconstants.success'),
                'message' => Config::get('appconstants.otp_success')
            ]);


    }

    public function verifyOTP(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->mobile)|| empty($request->otp)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

         $otp =	Otp::where('mobile',$request->mobile)->where('otp',$request->otp)->first();

			
         if ( $otp ) {

          //update verification status
          Customer::where('mobile',$request->mobile)->update(array('is_otp_verified' => 'true'));


         	return response()->json([
            'data' => null,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.otp_verify_success')
       		 ]);
         
         }
         else
         {
         	return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.invalid_otp')
            ]);
         }

    }

    public function getStates(Request $request)
    {

      $country_id = '105'; //India

      if (!empty($request->country_id)) {
        $country_id  = $request->country_id;
      }

        //We will fetch only indian states as app is for india only
        $allData = StateModel::where('country_id',$country_id)->get();


        return response()->json([
            'data' => $allData,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.state_list_success')
        ]);

    }

    public function getCities(Request $request)
    {

      $state_id = '13'; //Maharashtra

      if (!empty($request->state_id)) {
        $state_id  = $request->state_id;
      }

        //We will fetch only indian states as app is for india only
        $allData = CityModel::where('state_id',$state_id)->get();


        return response()->json([
            'data' => $allData,
            'status' => Config::get('appconstants.success'),
            'message' => Config::get('appconstants.state_list_success')
        ]);

    }

    /**
    * Feedback from user, make entry 
    */

    public function addFeedback(Request $request)
    {


        // Check parameter missing condition
        if (empty($request->rating) || empty($request->customer_id)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        FeedbackModel::create($request->all());
        $model = FeedbackModel::latest()->first();

      
         
          return response()->json([
              'data' => $model,
              'status' => Config::get('appconstants.success'),
              'message' => Config::get('appconstants.add_feedback_success')
            ]);
         

    }

    public function getCustomerFeedback(Request $request)
    {

        

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


        $allData = FeedbackModel::offset($offset)->limit($limit)->get();

        if($allData){
              return response()->json([
                   'data' =>  $allData,
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.get_feedback_list_success')
              ]);
        }
        else
        {
              return response()->json([
                   'data' =>  [],
                  'status' => Config::get('appconstants.success'),
                  'message' => Config::get('appconstants.get_feedback_list_success')
              ]);
        }

    }  

    protected function sendSms(Request $request)
    {

      $to       = $request->to;
      $message  = $request->message;

/*

//MSG91===========
        $curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://api.msg91.com/api/v2/sendsms?country=91&sender=&route=&mobiles=&authkey=&encrypt=&message=&flash=&unicode=&schtime=&afterminutes=&response=&campaign=",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{ \"sender\": \"BIKSER\", \"route\": \"4\", \"country\": \"91\", \"sms\": [ { \"message\": \"".$message."\", \"to\": [ \"". $to."\" ] } ] }",
  CURLOPT_SSL_VERIFYHOST => 0,
  CURLOPT_SSL_VERIFYPEER => 0,
  CURLOPT_HTTPHEADER => array(
    "authkey: 268140Abqx9ELPaSEf5c8f67f6",
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

*/

$this->sendSmsMsg($to,$message);


    }



}
