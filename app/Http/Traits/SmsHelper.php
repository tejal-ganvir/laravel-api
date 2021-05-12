<?php
namespace App\Http\Traits;

use App\Otp;
use Config;


trait SmsHelper
{


protected function sendSmsMsg($to,$message)
    {

     
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
		    "authkey: 268690AE7Ngu4kTfM5c946e5b",
		    "content-type: application/json"
		  ),
		));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			// if ($err) {
			//   echo "cURL Error #:" . $err;
			// } else {
			//   echo $response;
			// }

    }


    protected function resendOTPInternal($mobile)
    {


        // Check parameter missing condition
        if (empty($mobile)) {
              
            return response()->json([
              'data' => null,
              'status' => Config::get('appconstants.error'),
              'message' => Config::get('appconstants.param_missing')
            ]);
        }

        	Otp::where('mobile',$mobile)->delete();

			     $six_digit_random_number = mt_rand(100000, 999999);

        	$otprow = new Otp();
       		$otprow->otp = $six_digit_random_number;
       		$otprow->mobile = $mobile;
            $otprow->save();

          	$otprow = Otp::latest()->first();


            //$message = "Your OTP: ".$six_digit_random_number;

            $message = $six_digit_random_number.' is your One Time Password (OTP) to verify your Mobile Number for Narsinhakrupa Auto App';

            $this->sendSmsMsg($mobile,$message);




    	     return response()->json([
                'data' => $otprow,
                'status' => Config::get('appconstants.success'),
                'message' => Config::get('appconstants.otp_success')
            ]);


    }





}