<?php 


sendSmsNotification("9902233232_1","9902233232","990112");

function sendSmsNotification($username, $mobile, $pincode)
    {
        $textLocal = new \Drupal\rotary_api\TextLocalProvider('usha.cs@sahyadri.edu.in', 'Aptra2017', false);
        $mobile = "90849323";
        $numbers = array('+91' . $mobile);
        $sender = 'APTTCH';
        $message = "Dear Students, For your information " . "UserName: $username" . ' ' . "Pincode: $pincode" . " Thank You.";
        try {
            $result = $textLocal->sendSms($numbers, $message, $sender); } catch (\Exception $e) {
            die('Error1: ' . $e->getMessage());
        } catch (\Exception $e) {
            die('Error1: ' . $e->getMessage());
        }
    }

// function sendSmsNotification($username, $mobile , $pincode)
//     {
//         $apiKey = urlencode('/eDq5hPDeT8-vxFTUbHBTT9zWpRFVQiPwrRt9zGlqq');
//         echo $apiKey;
//         $numbers = array("+91".$mobile);
//         $sender = urlencode('APPTCH');
//         $msg = "Dear Students, For your information "."hi".' '."Hi"." Thank You.";
//         $message = rawurlencode($msg);
//         $numbers = implode(',', $numbers);
//         $data = 'apikey=' . $apiKey . '&numbers=' . $numbers . "&sender=" . $sender . "&message=" . $message;
 
//         $ch = curl_init('https://api.textlocal.in/send/?' . $data);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         $response = curl_exec($ch);
//         curl_close($ch);
//        var_dump($response);
//     }
