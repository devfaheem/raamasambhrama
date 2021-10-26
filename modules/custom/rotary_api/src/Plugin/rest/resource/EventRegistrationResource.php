<?php

namespace Drupal\rotary_api\Plugin\rest\resource;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Database as Database;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $pincode = random_int(100000, 999999);
        $errors = [];
        if($this->isDuplicateUserName($payload["mobile"]))
        {
            return new JsonResponse(["error"=>"Mobile number already registered."], 422);
        }
        # couple registration
        if($payload["registrationType"]=="12"){
            $ann = $payload["dependants"][0] ;
            if($ann["mobilenumber"]==$payload["mobile"]){
                return new JsonResponse(["error"=>"Rotarian and Ann's mobile numbers should not be same."], 422);
            }
        }

        if(count($errors)>0){
            return new JsonResponse(["error"=>"duplicate asdeasd"], 422);
        }

        $recieptId = $this->gerRecieptNumber();

        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $user = \Drupal\user\Entity\User::create();
        $user->setPassword($pincode);
        $user->setEmail($payload["mobile"]."@raamasambrama.com");
        $user->setUsername($payload["mobile"]);
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
        $user->set("field_payment_status", "InformationSubmitted");
        $user->set("field_password_raw", $pincode);
        $user->set("field_food_preference", $payload["foodprefs"]);
        $user->activate();

        // Couple Registration
        if($payload["registrationType"]=="12"){
           
            $ann = $payload["dependants"][0];
            if($ann["mobilenumber"]==$payload["mobile"])
            return new ModifiedResourceResponse(["error" => "duplicate mobile"], 422);
            if($this->isDuplicateUserName($ann["mobilenumber"]))
            {
                return new JsonResponse(["error"=>"Mobile number already registered for Ann."], 422);
            }
            $user2 = \Drupal\user\Entity\User::create();
            $user2->setPassword($pincode);
            $user2->setEmail($ann["mobilenumber"]."@raamasambrama.com");
            $user2->setUsername($ann["mobilenumber"]);
            $user2->addRole("registrant");
            $user2->set("field_reciept_id", $recieptId);
            $user2->set("field_password_raw", $pincode);
            $user2->set("field_registration_type", $payload["registrationType"]);
            $user2->set("field_registrant_name", $ann["fullname"]);
            $user2->set("field_current_designation", "Ann");
            $user2->set("field_mobile", $ann["mobilenumber"]);
            $user2->set("field_zone", $payload["zoneId"]);
            $user2->set("field_club", $payload["clubId"]);
            $user2->set("field_contact_address", $payload["contactAddress"]);
            $user2->set("field_payment_mode", $payload["paymentMode"]);
            $user2->set("field_payment_status", "InformationSubmitted");
            $user2->set("field_food_preference", $ann["foodprefs"]);
            $user2->activate();
        }
        

        $user->field_dependants = $this->getDependants($payload);
        $result = [];
        try {
            $result = $user->save();
            if($user2!=null)
            $result2 = $user2->save();
            return new ModifiedResourceResponse(["message"=>"Registered Successfully"], 200);
        } catch (\Drupal\Core\Entity\EntityStorageException $exception) {
            return new ModifiedResourceResponse(["error" => str_contains($exception->getMessage(),"1062 Duplicate entry")?"Mobile number already registered.":$exception->getMessage()], 422);
            // return
        }

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
