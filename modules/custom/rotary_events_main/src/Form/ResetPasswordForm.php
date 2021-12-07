<?php

namespace Drupal\rotary_events_main\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ResetPasswordForm.
 */
class ResetPasswordForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reset_password_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => '0',
      '#required' => TRUE,
      '#description'=> "Please enter your loginid for which you forgotten the password."
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    if(isset($_GET["loginid"]))
    {
      $form['username']["#default_value"] = $_GET["loginid"];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $values = $form_state->getValues();
    $username = $values["username"];
    $this->resendPassword($username);
  }

  public function resendPassword($loginId){
    try {
    $db = \Drupal::database();
    $query = "select uid from users_field_data where name = '$loginId'";
    $result = $db->query($query);
    $userid = $result->fetchField();
    $pincode = random_int(100000, 999999);
    $account = \Drupal\user\Entity\User::load($userid);
    if($account == null){
      \Drupal::messenger()->addError("Unable to reset the password for the login Id ".$loginId);
      return;
    }
    $mobile = $account->get('field_mobile')->getValue()[0]["value"];
    $pincode = $account->field_password_raw->value;
    $this->sendSmsNotification($loginId,$mobile,$pincode);
    \Drupal::messenger()->addMessage("If this user with login id ".$loginId." exists and has a mobile number registered, you will recieve an sms with the login details.");
    }
    catch(\Exception $e){
      \Drupal::messenger()->addMessage("Unable to reset the password for the login Id ".$loginId);
    }
  }

  public function sendSmsNotification($username, $mobile, $pincode)
  {
    $textLocal = new \Drupal\rotary_api\Controller\TextLocalProvider('usha.cs@sahyadri.edu.in', 'Aptra2017', false);
    $numbers = array('+91' . $mobile);
    $sender = 'APTTCH';
    $message = "Dear Member, You have successfully registered with DISCON 22. rotary3182events.org. Your login id $username and Password $pincode.APTTCH Rotary3182 Thank You.";
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
