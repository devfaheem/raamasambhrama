<?php
namespace Drupal\rotary_api\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class QRApiController extends ControllerBase
{
    function updateQRStatus(Request $request){
        $requestData = $request->getContent();
        $requestData = json_decode($requestData);
        $registrantId = $requestData->registrant_id;
        $deliverableId = $requestData->deliverable_id;
        $db = \Drupal::database();
        
        $q1 = "select count(*) as count from user_deliverables_entity where is_scanned = true and registrant_id = '$registrantId' and deliverable = '$deliverableId'";
        
        $r1 = $db->query($q1);
        $r1 = $r1->fetchField();
        if($r1>0)
        {
            return new JsonResponse(["message"=>"Already Scanned","Status"=>"Failure"]);
        }
        $status = $db->update("user_deliverables_entity")
        ->fields(["is_scanned" => true,"scanned_date"=> date("Y-m-d H:i:s")])
        ->condition("registrant_id", $registrantId)
        ->condition("deliverable", $deliverableId)
        ->execute();
        
        

        $query ="select ufr.field_registrant_name_value as fullname,
        ufd.name,ude.scanned_date
        from user_deliverables_entity as ude
        right join users_field_data as ufd on ude.registrant_id = ufd.uid
        right join user__field_registrant_name as ufr on ude.registrant_id = ufr.entity_id
        where is_scanned = true and registrant_id = '$registrantId' and deliverable = '$deliverableId'";
        $result = $db->query($query);
        $result = $result->fetchAll()[0];
        return new JsonResponse(["data"=>$result,"message"=>"Updated Record Successfully" , "status"=>"Success"]);
    }

    function getScannedQrCodes(Request $request){
        $deliverableId = $request->get("deliverable_id");
        $limit = 10;
        $page = $request->get("page");
        $page = $limit*$page;
        $query ="select ufr.field_registrant_name_value as fullname,
        ufd.name,ude.scanned_date
        from user_deliverables_entity as ude
        right join users_field_data as ufd on ude.registrant_id = ufd.uid
        right join user__field_registrant_name as ufr on ude.registrant_id = ufr.entity_id
        where is_scanned = true and deliverable = '$deliverableId' limit $limit offset $page ";
        $db = \Drupal::database();
        $result = $db->query($query);
        $result = $result->fetchAll();
        return new JsonResponse($result);
    }
}
