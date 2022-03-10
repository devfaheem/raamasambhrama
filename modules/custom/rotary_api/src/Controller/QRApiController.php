<?php
namespace Drupal\rotary_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class QRApiController extends ControllerBase
{
    function updateScanStatus(Request $request){
        $requestData = $request->getContent();
        $requestData = json_decode($requestData);
        $registrantId = $requestData->registrant_id;
        $deliverableId = $requestData->deliverable_id;
        $foodCounterType = $requestData->food_counter_type;
        $foodCounterChanged = $requestData->food_counter_changed;
        $db = \Drupal::database();
        $q1 = "select count(*) as count from user_deliverables_entity where is_scanned = true and registrant_id = '$registrantId' and deliverable = '$deliverableId'";
        $r1 = $db->query($q1);
        $r1 = $r1->fetchField();
        if($r1>0)
        {
            return new JsonResponse(["message"=>"Already Scanned","status"=>"Failure"]);
        }
        $status = $db->update("user_deliverables_entity")
        ->fields([
        "is_scanned" => true,
        "scanned_date"=> date("Y-m-d\TH:i:s"),
        "food_counter"=>$foodCounterType,
        "food_counter_changed"=>$foodCounterChanged?1:0
        ])
        ->condition("registrant_id", $registrantId)
        ->condition("deliverable", $deliverableId)
        ->execute();


        $userDetails = $this->getUserDetail($registrantId, $deliverableId);
        if($request->deliverable_id == "708" ||  $request->deliverable_id == 708){
            $this->recieveMomentoSMS($requestData->registrant_id);
        }
        if($request->deliverable_id == "706" ||  $request->deliverable_id == 706){
            $this->recieveIdCardSMS($userDetails["mobile"]);
        }
        return new JsonResponse(["data"=>$userDetails,"message"=>"Updated Record Successfully" , "status"=>"Success"]);
    }

    function recieveIdCardSMS($userid){
        $textLocal = new \Drupal\rotary_api\Controller\TextLocalProvider('usha.cs@sahyadri.edu.in', 'Aptra2017', false);
        $mobile = $this->getMobileNumber($userid);
        if($mobile == null) { return;}
        $numbers = array($mobile);
        $sender = 'APTTCH';
        $message2 = "Dear delegate, Your code-tag scanned for Receiving ID Card. This is for your information. Thank you. Team Raama Sambhrama. APTTCH";
        try {

            $result = $textLocal->sendSms($numbers, $message2, $sender);
        } catch (\Exception $e) {
        }
    }

    function recieveMomentoSMS($userid){
        $textLocal = new \Drupal\rotary_api\Controller\TextLocalProvider('usha.cs@sahyadri.edu.in', 'Aptra2017', false);
        $mobile = $this->getMobileNumber($userid);
        if($mobile == null) { return;}
        $numbers = array($mobile);
        $sender = 'APTTCH';
        $message2 = "Dear delegate, Your code-tag scanned for Receiving Event Momento. This is for your information. Thank you. Team Raama Sambhrama. APTTCH";
        try {
            $result = $textLocal->sendSms($numbers, $message2, $sender);
        } catch (\Exception $e) {
        }
    }

    
    function getMobileNumber($userid){
        $db = \Drupal::database();
        $query = "select field_mobile_value as mobile from user__field_mobile where entity_id = $userid";
        $result = $db->query($query);
        $result = $result->fetchObject();
        try{
            return $result["mobile"];
        }
        catch(\PDOException $e){
            return null;
        }
    }

    function updateCheckinStatus(Request $request){
        $requestData = $request->getContent();
        $requestData = json_decode($requestData);
        $registrantId = $requestData->registrant_id;
        $deliverableId = $requestData->deliverable_id;
        $db = \Drupal::database();
        $q1 = "select checkindata, ischeckedin from user_deliverables_entity where registrant_id = '$registrantId' and deliverable = '$deliverableId'";
        $r1 = $db->query($q1);
        $checkin = $r1->fetchObject();
        $check = [];
        $date = date("Y-m-d H:i:s");
        $check["created_at"] = $date;
        $status = "";
        $checkindata = null;
        if($checkin->ischeckedin == "yes")
        {
            $status = "no";
            $check["action"] = "checkedout";
        }
        else{
            $status = "yes";
            $check["action"] = "checkedin";
        }
        
        try{
        $checkindata = json_decode($checkin->checkindata);
        }
        catch(\Exception $e){
            $checkindata = json_decode("[]");
        }
        $checkindata[] = $check;
        
        $db->update("user_deliverables_entity")
        ->fields([
        "is_scanned"=>true,    
        "ischeckedin" => $status,
        "scanned_date"=> date("Y-m-d\TH:i:s"),
        "checkindata"=> json_encode($checkindata)
        ])
        ->condition("registrant_id", $registrantId)
        ->condition("deliverable", $deliverableId)
        ->execute();
        $userDetails = $this->getUserDetail($registrantId, $deliverableId);
        return new JsonResponse(["data"=>$userDetails,"message"=>"Updated Record Successfully" , "status"=>"success","checked_in"=>$q1]);
    }

    function getUserDetails(Request $request){
        $deliverableId = $request->get("deliverable_id");
        $registrantUuidd = $request->get("uuid");
        $db = \Drupal::database();
        $q1 = "select uid from users where uuid = '$registrantUuidd'";
        $r1 = $db->query($q1);
        $registrantId = $r1->fetchField();
        $result = $this->getUserDetail($registrantId, $deliverableId);
        
        return new JsonResponse($result);
    }

    function getScannedQrCodes(Request $request){
        $deliverableId = $request->get("deliverable_id");
        $query ="        select 
        users.uid as uid,
        users.uuid as uuid,
        rname.field_registrant_name_value as fullname,
        clubName.name as club,
        zoneName.name as zone,
        amount.field_amount_value as amount,
        registrationTypeName.name as registration_type,
        fprefs.field_food_preference_value as food_preference,
        ude.scanned_date as scanned_date,
        ude.is_scanned,
        ude.ischeckedin,
        ude.deliverable
        from user_deliverables_entity as ude
        left join users as users on users.uid = ude.registrant_id
        left join users_field_data as ufd on ufd.uid = ude.registrant_id
        left join user__field_registrant_name as rname on rname.entity_id = ude.registrant_id
        left join user__field_club as club on club.entity_id = ude.registrant_id
        left join user__field_zone as zone on zone.entity_id = ude.registrant_id
        left join user__field_amount as amount on amount.entity_id = ude.registrant_id
        left join user__field_registration_type as rtype on rtype.entity_id = ude.registrant_id
        left join user__field_food_preference as fprefs on fprefs.entity_id = ude.registrant_id
        left join taxonomy_term_field_data as clubName on clubName.tid = club.field_club_target_id
        left join taxonomy_term_field_data as zoneName on zoneName.tid = zone.field_zone_target_id
        left join taxonomy_term_field_data as registrationTypeName on registrationTypeName.tid = rtype.field_registration_type_target_id
        where is_scanned = true and deliverable = '$deliverableId' order by scanned_date desc";
        $db = \Drupal::database();
        $result = $db->query($query);
        $result = $result->fetchAll();
        return new JsonResponse($result);
    }


    function getUserDetail($registrantId, $deliverableId){
        $query =  "
        select 
        users.uid as uid,
        users.uuid as uuid,
        rname.field_registrant_name_value as fullname,
        clubName.name as club,
        zoneName.name as zone,
        amount.field_amount_value as amount,
        registrationTypeName.name as registration_type,
        fprefs.field_food_preference_value as food_preference,
        ude.scanned_date as scanned_date,
        ude.is_scanned,
        ude.ischeckedin,
        ude.deliverable
        from user_deliverables_entity as ude
        left join users as users on users.uid = ude.registrant_id
        left join users_field_data as ufd on ufd.uid = ude.registrant_id
        left join user__field_registrant_name as rname on rname.entity_id = ude.registrant_id
        left join user__field_club as club on club.entity_id = ude.registrant_id
        left join user__field_zone as zone on zone.entity_id = ude.registrant_id
        left join user__field_amount as amount on amount.entity_id = ude.registrant_id
        left join user__field_registration_type as rtype on rtype.entity_id = ude.registrant_id
        left join user__field_food_preference as fprefs on fprefs.entity_id = ude.registrant_id
        left join taxonomy_term_field_data as clubName on clubName.tid = club.field_club_target_id
        left join taxonomy_term_field_data as zoneName on zoneName.tid = zone.field_zone_target_id
        left join taxonomy_term_field_data as registrationTypeName on registrationTypeName.tid = rtype.field_registration_type_target_id
        where ude.registrant_id = '$registrantId'  and ude.deliverable = '$deliverableId'";
        $db = \Drupal::database();
        $r2 = $db->query($query);
        $result = $r2->fetchObject();
        return $result;
    }
}
