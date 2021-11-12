<?php

namespace Drupal\rotary_events_main\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class RegistrationController.
 */
class RegistrationController extends ControllerBase
{

  /**
   * Registration.
   *
   * @return string
   *   Return Hello string.
   */
  public function registrationForm()
  {
    return   $build = [
      '#theme' => 'single_registration_form'
    ];
  }

  public function multipleRegistrationForm()
  {
    return   $build = [
      '#theme' => 'multiple_registration_form'
    ];
  }

  public function resetPincode(Request $request)
  {
    try {
      $requestData = $request->getContent();
    $requestData = json_decode($requestData);
    $loginId = $requestData->loginId;
    $db = \Drupal::database();
    $query = "select uid from users_field_data where name = '$loginId'";
    $result = $db->query($query);
    $userid = $result->fetchField();
    $pincode = random_int(100000, 999999);
    $account = \Drupal\user\Entity\User::load(1);
    $account->setPassword($pincode);
    $account->set("field_password_raw", $pincode);
    $mobile = $account->get('field_mobile')->getValue()[0]["value"];
    $account->save();
    $this->sendSmsNotification($loginId,$mobile,$pincode);
    return new JsonResponse(["message"=>"Pincode Reset Successfully"], 200);

    }
    catch(\Exception $e){
      return new JsonResponse([], 200);
    }
  }

  public function sendSmsNotification($username, $mobile, $pincode)
  {
    $textLocal = new \Drupal\rotary_api\Controller\TextLocalProvider('usha.cs@sahyadri.edu.in', 'Aptra2017', false);
    $numbers = array('+91' . $mobile);
    $sender = 'APTTCH';
    $message = "Dear Member, Please note your credentials UserName: $username and Pincode: $pincode. Thank you. APTTCH";
    // $message = "Dear Member, Please note your credentials UserName: " . "UserName: $username" . ' ' . "Pincode: $pincode" . " Thank You.";
    try {
      $result = $textLocal->sendSms($numbers, $message, $sender);
    } catch (\Exception $e) {
      die('Error1: ' . $e->getMessage());
    } catch (\Exception $e) {
      die('Error1: ' . $e->getMessage());
    }
  }
}
