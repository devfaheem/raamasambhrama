<?php

class CreateUserScript{

public function create($payload)
{   
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
    $user->set("field_payment_status", "ConfirmedRegistration");
    $user->set("field_food_preference", $payload["foodprefs"]);
    $user->set("field_amount", $payload["amount"]);
    $user->activate();
    try {
        $result = $user->save();
        $this->generateDeliverables($user->id(),$payload["zoneId"],$payload["clubId"]);
        echo "user created successfully";
    } catch (\Drupal\Core\Entity\EntityStorageException $exception) {
        echo "failed to create user";
    }

}

public function generateDeliverables($userId, $zoneId, $clubId){
    $db = \Drupal::database();
    $deliverables = $db->query("select nid,type from node where type = 'deliverable'");
    $deliverables = $deliverables->fetchCol();
    foreach($deliverables as $deliverable){
        $this->generateDeliverable($userId,$zoneId,$clubId,$deliverable);
    }
    echo "deliverables generated successfully";
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
}