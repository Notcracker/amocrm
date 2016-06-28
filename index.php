<?php 

$leads = array();
$leadOffset = 0;
$tasks = array();
$taskOffset = 0;
$errors=array(
    301=>'Moved permanently',
    400=>'Bad request',
    401=>'Unauthorized',
    403=>'Forbidden',
    404=>'Not found',
    500=>'Internal server error',
    502=>'Bad gateway',
    503=>'Service unavailable'
  );


$subdomain='test';

//fetching results;
function curlSetup($link,$errors)
{
  
  $curl=curl_init(); 
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
  curl_setopt($curl,CURLOPT_URL,$link);
  curl_setopt($curl,CURLOPT_HEADER,false);
  curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); _
  curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); 
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
  $out=curl_exec($curl); 
  $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  curl_close($curl);
  $code=(int)$code;
  
  try
  {
    if($code!=200 && $code!=204)
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
  }
  catch(Exception $E)
  {
    die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
  }

  $Response=json_decode($out,true);
  return $Response;
}



//getting leads
do {
  $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/leads/list?limit_rows=500&limit_offset='.$leadOffset;

  $Response = curlSetup($link,$errors);

  $leadsFromThisReq=$Response['response']['leads'];
  $leads = array_merge($leads,$leadsFromThisReq);
  $leadOffset += 500;
  sleep(1);
} while(count($leadsFromThisReq)==500);

//getting tasks
do { 
  $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/list?type=lead&limit_rows=500&limit_offset='.$taskOffset;

  $Response = curlSetup($link,$errors);

  $tasksFromThisReq = $Response['response']['tasks'];
  $tasks = array_merge($tasks,$tasksFromThisReq);
  $taskOffset += 500;
  sleep(1);
} while(count($tasksFromThisReq)==500);

//deleting leads with tasks from our array
foreach ($tasks as $task) {
  foreach ($leads as $key=>$lead) {
    if ($task['element_id']==$lead['id']) {
      unset($leads[$key]);
    }
  }
}

//creating tasks for leads without them

foreach ($leads as $lead) {
  $newTask['request']['tasks']['add']=array(
    array(
      'element_id'=>3698754, 
      'element_type'=>2,
      'task_type'=>1, #Звонок
      'text'=>'Сделка без задачи',
      'complete_till'=>1375285346
    )
  );
  $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/set';
  curl=curl_init();
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
  curl_setopt($curl,CURLOPT_URL,$link);
  curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
  curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($newTask));
  curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
  curl_setopt($curl,CURLOPT_HEADER,false);
  curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); 
  curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); 
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
   
  $out=curl_exec($curl); 
  $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  $code=(int)$code;

  try
  {
    
    if($code!=200 && $code!=204)
      throw new Exception(isset($errors[$code]) ? $errors[$code] : 'Undescribed error',$code);
  }
  catch(Exception $E)
  {
    die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
  }
   
 
  $Response=json_decode($out,true);
  $Response=$Response['response']['tasks']['add'];
   
  $output='ID добавленных задач:'.PHP_EOL;
  foreach($Response as $v)
    if(is_array($v))
      $output.=$v['id'].PHP_EOL;
  return $output;
  }

?>