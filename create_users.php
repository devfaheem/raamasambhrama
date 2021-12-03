<?php 
require 'create_user_class.php';
use Drupal\rest\ModifiedResourceResponse;

$inputFileName  = "rotary_users_data_20_11_2021.xlsx";
$inputFileType = 'Xlsx';
/**  Create a new Reader of the type defined in $inputFileType  **/
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
/**  Load $inputFileName to a Spreadsheet Object  **/
$spreadsheet = $reader->load($inputFileName);
$worksheet = $spreadsheet->getActiveSheet();
$rows = $worksheet->toArray();
$data = [];
$createScript = new CreateUserScript();
foreach($rows as $registrant) {
   
    if($registrant[6] == null)
    continue;
    $payload = [];
    $payload["reciept_id"] = $registrant[1];
    $payload["registrationType"] = getRegistrationType($registrant[5]);
    $payload["registrantName"] = $registrant[6];
    $payload["currentDesignation"] = $registrant[5];
    $payload["mobile"] = strval($registrant[7]);
    $payload["zoneId"] = getZone($registrant[3]);
    $payload["clubId"] = getClub($registrant[4]);
    $payload["contactAddress"] = "Not Available";
    $payload["paymentMode"] = "directbanktransfer";
    $payload["foodprefs"] = getFoodPrefs($registrant[11]);
    $payload["amount"] = $registrant[8];

    $data[]=$payload;
    $createScript->create($payload);
};


function getFoodPrefs($val){
    if($val == "Non Veg"){
        return "nonveg";
    }else if ($val == "Veg"){
        return "veg";
    }
    echo "FoodPrefs: ".$val." end \n";
    return $val;
}

function getRegistrationType($val){
    switch($val){
        case "Ann" : return "10";
                        break;
        case "Rtn" : return "9";
                        break;
        case "Annet" : return "11";
                        break;
        echo "RegType: ".$val;
        default: return $val;
    }
}


function getDesignation($val){
    switch($val){
        case "Ann" : return "Ann";
                        break;
        case "Rtn" : return "Rotarian";
                        break;
        case "Annet" : return "Annet";
                        break;
                        
        echo "Designation: ".$val;
        default: return $val;
    }
}

function getZone($val){
    if(!$val){
        return $val;
    }
    $zoneId = getTidByName("Zone ".$val);
    return $zoneId;
}

function getClub($val){
    if(!$val){
        return $val;
    }
    $val = fixClubName($val);
    $clubId = getTidByName($val);
    if($clubId==0)
    echo $val." = ". $clubId."\n";
    return $clubId;
}

function getTidByName($name = NULL, $vid = NULL) {
    $properties = [];
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);

    return !empty($term) ? $term->id() : 0;
  }

  function fixClubName($val){
    if($val == "Shimoga Riverside")
    {
        $val = "Shimoga River Side";
    }
    if($val == "R C Bhadravathi")
    {
        $val = "Bhadravathi";
    }
    if(trim($val) == "Sagara")
    {
        $val = "Sagar";
    }
    if($val == "Koppa Malnad")
    {
        $val = "Koppa Malnadu";
    }
    if($val == "Chickmagalur CoffeeLand")
    {
        $val = "Chikmagalur Coffee Land";
    }
    if($val == "Kundapura Riverside")
    {
        $val = "Kundapura River Side";
    }
    if($val == "Kallyanapura")
    {
        $val = "Kallianapur";
    }
    if($val == "RC Manipal")
    {
        $val = "Manipal";
    }
    if($val == "RC Brahmavar")
    {
        $val = "Brahmavara";
    }
    if($val == "RC Byndoor")
    {
        $val = "Byndoor";
    }
    if($val == "RC Byndoor")
    {
        $val = "Byndoor";
    }
    if($val == "N R Pura")
    {
        $val = "Narasimharajapura";
    }
    if($val == "udupi Mid Town")
    {
        $val = "Udupi Mid-Town";
    }
    if($val == "Royal Bramhavara")
    {
        $val = "Royal Brahmavara";
    }
    if($val == "Kundapura Mid Town")
    {
        $val = "Kundapura Mid-Town";
    }
    if($val == "R C Kundapura ")
    {
        $val = "Kundapura";
    }
    if($val == "Shringeri")
    {
        $val = "Sringeri";
    }
    if($val == "Thekkate")
    {
        $val = "Thekkatte";
    }
    if($val == "Hangarkatta sastan")
    {
        $val = "HangarkattaSastan";
    }
    if($val == "Kota Saligrama")
    {
        $val = "Kota - Saligrama";
    }
    return $val;
  }