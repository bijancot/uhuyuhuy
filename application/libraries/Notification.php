<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Notification {

    public function push($param){
        $curl = curl_init();
        $authkey = 'key=AAAAeaYXr4M:APA91bHbyfgMLwsCBGeYRTNC2oXGq_al-hKDVIyK-DxV8cljHqxIhpF9Rp27AJGxS1gDeICW6dx04WnPEoBEzlrm_pIa5NVm7sbn3McSzfrg2hvl3EN9Afg0KaZWTv7EMyG2-hjRbVQJ';
        $regisIds = array();
        foreach($param['regisIds'] as $item){
          if($item['TOKEN_USERS'] != null){
            array_push($regisIds, '"'.$item['TOKEN_USERS'].'"');
          }
        }
        $regisIds = implode(',', $regisIds);

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
          "registration_ids":['.$regisIds.'],
          "notification": {
              "title":"'.$param['title'].'",
              "body":"'.$param['message'].'",
              "icon":"myicon",
              "sound":"default"
          }
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: '.$authkey
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
}
