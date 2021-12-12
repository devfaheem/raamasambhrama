<?php 

$userStorage = \Drupal::entityTypeManager()->getStorage('user');

    $query = $userStorage->getQuery();
    $uids = $query
        ->condition('status', '1')
        ->condition('roles', 'registrant')
        ->condition('roles', 'registrant')
        ->execute();

    $users = $userStorage->loadMultiple($uids);
    
    foreach ($users as $key=>$user){
        // var_dump($user->field_payment_status->value);
        $user->set("field_management_verified", "no");
        $user->save();
    }