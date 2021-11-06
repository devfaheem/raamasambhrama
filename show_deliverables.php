<?php 
$db = \Drupal::database();
$result = $db->query("select nid,type from node where type = 'deliverable'");
$result = $result->fetchCol();

$user = \Drupal\user\Entity\User::load(1);
echo $user->id();

// generateDeliverables(1, 2, 71);

 function generateDeliverables($userId, $zoneId, $clubId){
    $db = \Drupal::database();
    $deliverables = $db->query("select nid,type from node where type = 'deliverable'");
    $deliverables = $deliverables->fetchCol();
    foreach($deliverables as $deliverable){
        generateDeliverable($userId,$zoneId,$clubId,$deliverable);
    }
}

function generateDeliverable($userId, $zoneId, $clubId, $deliverableId){
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
    var_dump($entity->id());
  }