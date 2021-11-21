<?php

namespace Drupal\rotary_api\Plugin\rest\resource;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database as Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "event_registration_resource",
 *   label = @Translation("Event registration resource"),
 *   uri_paths = {
 *     "create" = "/event/registration"
 *   }
 * )
 */
class EventRegistrationResource extends ResourceBase
{
    public $zone;
    public $club;
    public $paymentMode;

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
        $instance->logger = $container->get('logger.factory')->get('rotary_api');
        $instance->currentUser = $container->get('current_user');
        return $instance;
    }

    /**
     * Responds to POST requests.
     *
     * @param string $payload
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post($payload)
    {   
        // if(!isset($payload["recaptchaToken"])||!$this->verifyRecaptcha($payload["recaptchaToken"])){
        //     return new ModifiedResourceResponse(["message"=>"Invalid Captcha"], 401);
        // }

        $registrationType = \Drupal::request()->query->get('registrationtype');
        
        if($registrationType=="single" || $registrationType==null)
        return $this->singleRegistration($payload);
        else if($registrationType=="multiple")
        {
            $this->club = $payload["club"];
            $this->zone = $payload["zone"];
            $this->paymentMode = $payload["paymentMode"];
            $registrations = $payload["registrations"];
            return $this->multipleRegistration($registrations);
        }
    }

    public function verifyRecaptcha($token){
        $secretKey = "6LeSgUUdAAAAAPvBig1O4_Tfx2gQLxy3psEY4Y7c";
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array('secret' => $secretKey, 'response' => $token);
        $options = array(
            'http' => array(
              'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
              'method'  => 'POST',
              'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $responseKeys = json_decode($response,true);
        if($responseKeys["success"]) {
            return true;
          } else {
            return false;
        }
    }

    public function createAcknowledgement($picture, $utrnumber, $userId){
        $entityType = "payment_acknowledgement";
        $bundleName = "default";
        $fid = !in_array($picture,["",null])?$this->savePaymentAckPicture($picture):null;
        $acknowlegement = entity_create($entityType, array('type' => $bundleName));
        $acknowlegement->set("field_image_upload", $fid);
        $acknowlegement->set("field_reference_number", $utrnumber);
        $acknowlegement->set("uid", $userId);
        $acknowlegement->save();
        $user = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
        $user->set("field_payment_status", "PendingFinanceReview");
        $user->save();
        
    }

    public function savePaymentAckPicture($picture){
        $base64Image = $picture;
        $base64Image = trim($base64Image);
        $base64Image = str_replace('data:image/png;base64,', '', $base64Image);
        $base64Image = str_replace('data:image/jpg;base64,', '', $base64Image);
        $base64Image = str_replace('data:image/jpeg;base64,', '', $base64Image);
        $base64Image = str_replace('data:image/gif;base64,', '', $base64Image);
        $extension = explode('/', mime_content_type($picture))[1];
        $base64DecodedFile = base64_decode($base64Image);
        $path ='public://acknowledgements/'.uniqid().".".$extension;
        $file = file_save_data($base64DecodedFile, $path, FILE_EXISTS_RENAME);
        return $file->id();
    }

    public function multipleRegistration($users){
        $connection = \Drupal::database();
        $transaction = $connection->startTransaction();
        try{
            foreach($users as $payload){
                $this->registerUser($payload);
            }
            return new ModifiedResourceResponse(["message"=>"Users Registered Successfully"], 200);
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            return new ModifiedResourceResponse(["message"=>"No Users Registered"], 422);;
          }
    }

    public function registerUser($payload){
        $userName = $this->getUserName($payload["mobile"]);
        $pincode = random_int(100000, 999999);
        $registrationType = $payload["registrationType"];        
        $recieptId = $this->gerRecieptNumber();
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword($pincode);
        $user->set("field_password_raw", $pincode);
        $user->setEmail($userName."@raamasambrama.com");
        $user->setUsername($userName);
        $user->addRole("registrant");
        $user->set("field_reciept_id", $recieptId);
        $user->set("field_registration_type", $payload["registrationType"]);
        $user->set("field_registrant_name", $payload["registrantName"]);
        $user->set("field_current_designation", $payload["currentDesignation"]);
        $user->set("field_mobile", $payload["mobile"]);
        $user->set("field_zone", $this->zone);
        $user->set("field_club", $this->club);
        $user->set("field_contact_address", $payload["contactAddress"]);
        $user->set("field_payment_mode", $this->paymentMode);
        $user->set("field_payment_status", "PendingFinanceReview");
        $user->set("field_food_preference", $payload["foodprefs"]);
        $user->activate();
        $result = $user->save();
        $this->generateDeliverables($user->id(),$payload["zoneId"],$payload["clubId"]);
        $this->sendSmsNotification($userName,$payload["mobile"],$pincode);
    }

    public function singleRegistration($payload){
        $ackSubmitted = !in_array($payload["payment_acknowledgement"], ["",null]) || !in_array($payload["utrnumber"], ["",null]);
        $userName = $this->getUserName($payload["mobile"]);
        $pincode = random_int(100000, 999999);
        $registrationType = $payload["registrationType"];        
        $recieptId = $this->gerRecieptNumber();
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword($pincode);
        $user->set("field_password_raw", $pincode);
        $user->setEmail($userName."@raamasambrama.com");
        $user->setUsername($userName);
        $user->addRole("registrant");
        $user->set("field_reciept_id", $recieptId);
        $user->set("field_registration_type", $payload["registrationType"]);
        $user->set("field_registrant_name", $payload["registrantName"]);
        $user->set("field_current_designation", $payload["currentDesignation"]);
        $user->set("field_mobile", $payload["mobile"]);
        $user->set("field_zone", $payload["zoneId"]);
        $user->set("field_club", $payload["clubId"]);
        $user->set("field_contact_address", $payload["contactAddress"]);
        $user->set("field_payment_mode", $payload["paymentMode"]);
        $user->set("field_payment_status", $ackSubmitted?"PendingFinanceReview":"InformationSubmitted");
        $user->set("field_food_preference", $payload["foodprefs"]);
        $user->set("field_amount", $payload["amount"]);
        $user->activate();

        // Couple Registration
        if($registrationType=="12"){
            $annsPincode = random_int(100000, 999999);
            $ann = $payload["dependants"][0] ;
            $annUserName = $ann["mobilenumber"];
            if($annUserName == $payload["mobile"])
            {
                $annUserName = $ann["mobilenumber"]."_".$this->addPrefix($userName);
            }
            $ann = $payload["dependants"][0];
            $user2 = \Drupal\user\Entity\User::create();
            $user2->setPassword($annsPincode);
            $user2->setEmail($annUserName."@raamasambrama.com");
            $user2->setUsername($annUserName);
            $user2->addRole("registrant");
            $user2->set("field_reciept_id", $recieptId);
            $user2->set("field_password_raw", $annsPincode);
            $user2->set("field_registration_type", $payload["registrationType"]);
            $user2->set("field_registrant_name", $ann["fullname"]);
            $user2->set("field_current_designation", "Ann");
            $user2->set("field_mobile", $ann["mobilenumber"]);
            $user2->set("field_zone", $payload["zoneId"]);
            $user2->set("field_club", $payload["clubId"]);
            $user2->set("field_contact_address", $payload["contactAddress"]);
            $user2->set("field_payment_mode", $payload["paymentMode"]);
            $user2->set("field_payment_status", $ackSubmitted?"PendingFinanceReview":"InformationSubmitted");
            $user2->set("field_food_preference", $ann["foodprefs"]);
            $user2->set("field_amount", $payload["amount"]);
            $user2->activate();
        }
        
        try {
            $user->save();
            $this->generateDeliverables($user->id(),$payload["zoneId"],$payload["clubId"]);
            $this->sendSmsNotification($userName,$payload["mobile"],$pincode);
            if($user2!=null){
                $result2 = $user2->save();
                $this->generateDeliverables($user2->id(),$payload["zoneId"],$payload["clubId"]);
                $this->sendSmsNotification($annUserName,$ann["mobilenumber"],$annsPincode);    
            }
            if($ackSubmitted){
                $this->createAcknowledgement($payload["payment_acknowledgement"],$payload["utrnumber"],$user->id());
                if($user2!=null)
                $this->createAcknowledgement($payload["payment_acknowledgement"],$payload["utrnumber"],$user2->id());
            }
            return new ModifiedResourceResponse(["message"=>"Registered Successfully"], 200);
        } catch (\Drupal\Core\Entity\EntityStorageException $exception) {
            return new ModifiedResourceResponse(["error" => str_contains($exception->getMessage(),"1062 Duplicate entry")?"Mobile number already registered.":$exception->getMessage()], 422);
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
            $result = $textLocal->sendSms($numbers, $message, $sender); } catch (\Exception $e) {
            die('Error1: ' . $e->getMessage());
        } catch (\Exception $e) {
            die('Error1: ' . $e->getMessage());
        }
    }

    public function generateDeliverables($userId, $zoneId, $clubId){
        $db = \Drupal::database();
        $deliverables = $db->query("select nid,type from node where type = 'deliverable'");
        $deliverables = $deliverables->fetchCol();
        foreach($deliverables as $deliverable){
            $this->generateDeliverable($userId,$zoneId,$clubId,$deliverable);
        }
    }

    public function generateDeliverable($userId, $zoneId, $clubId, $deliverableId){
        $entityType = "user_deliverables_entity";
        $bundleName = "default";
        $entity = entity_create($entityType, array('type' => $bundleName));
        $entity->set("name","User Deliverable");
        $entity->set("registrant_id",$userId);
        $entity->set("deliverable",$deliverableId);
        $entity->set("zone",$zoneId);
        $entity->set("club",$clubId);
        $entity->set("is_scanned",FALSE);
        $entity->set("status",TRUE);
        $entity->set("user_id",1);
        $entity->save();
    }

    public function getUserName($mobile){
        $db = \Drupal::database();
        $query = " select name,created as uname from users_field_data as u right join user__field_mobile as um on um.entity_id  = u .uid where um.field_mobile_value = '$mobile' order by created desc,name desc";
        $result = $db->query($query);
        try{
            $result = $result->fetchAll();
            $val = $result[0]->name;
        }
        catch(\Exception $e){
            return $mobile;
        }
        if($val == null) {
            return $mobile;
        }
        $prefix = $this->addPrefix($val);
        return $mobile."_".$prefix;
    }

    public function addPrefix($username){
        if(substr($username, -2,-1) == "_")
        $prefix = substr($username, -1)+1;
        else
        $prefix = substr($username, -2)+1;
        return $prefix;
    }

    public function gerRecieptNumber(){
        $db = \Drupal::database();
        $result = $db->query("select (max(field_reciept_id_value)+1) as reciept_id from user__field_reciept_id");
        return $result->fetchField();
    }

    public function isDuplicateUserName($mobile){
        $db = \Drupal::database();
        $query = "select count(*) as count from user__field_mobile where field_mobile_value = '$mobile' ";
        $result = $db->query($query);
        $count = $result->fetchField();

        if($count>0){
            return true;
        }
        return false;
    }

    public function getDependants($payload)
    {
        $dependants = [];
        foreach ($payload["dependants"] as $dependant) {
            $paragraph = Paragraph::create([
                'type' => 'dependants',
                'field_food_preferences' => array(
                    "value" => $dependant["foodprefs"],
                ),
                'field_mobile' => array(
                    "value" => $dependant["mobilenumber"],
                ),
                'field_name' => array(
                    "value" => $dependant["fullname"],
                ),
            ]);
            $paragraph->save();
            $dependants[] = [
                'target_id' => $paragraph->id(),
                'target_revision_id' => $paragraph->getRevisionId(),
            ];
        }
        return $dependants;
    }

}
