<?php

namespace  App\Http\Controllers;

use App\Models\PaidUser;
use App\Models\Proforma;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MessagingNotification extends Controller
{
    public function sendNotification()
    {
      /** We use php cURL for the samples **/
    $ch = curl_init();
    // base url
	$url = 'https://api.afromessage.com/api/send';
	$token = env('AFROMESSAGE_TOKEN');
	$to = request('to'); // Recipient phone number from request
    $from = env('IDENTIFIER_ID');
    $sender = env('SENDER_NAME');
    // message should be URL encoded
	$message = curl_escape($ch, request('message', 'YOUR_MESSAGE'));
	$callback = env('AFROMESSAGE_CALLBACK');

    /** set request options **/
	curl_setopt($ch, CURLOPT_URL, $url . '?from=' . $from . '&sender=' .$sender . '&to=' . $to . '&message=' . $message . '&callback=' . $callback);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    /** Add headers **/
	$headers = array();
	$headers[] = 'Authorization: Bearer '.$token;
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    /** Execute request **/
	$result = curl_exec($ch);

    /** Handle response **/
	if (curl_errno($ch)) {
        // general http error
		echo 'Error:' . curl_error($ch);
    } else {	
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		switch ($http_code) {
	    case 200:
                /** response object ... inspect the `acknowledge` node and act accordingly **/
				$data = json_decode($result,true);
				if ($data['acknowledge'] == 'success') {
					echo "Api success";
                }else{
					echo "Api failure";
                }
				break;
	    default:
          /** most probably authorization error ... inspect response**/
	      echo 'Other HTTP Code: ', $http_code;
        }
    }
    /** finish call **/
	curl_close ($ch);
}
}