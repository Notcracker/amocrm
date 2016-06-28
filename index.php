<?php 
$Tasks = array();
do {
  $subdomain='test'; 
  $link='https://'.$subdomain.'.amocrm.ru/private/api/v2/json/tasks/list?type=lead&limit_rows=500&limit_offset='.$offset;
  $curl=curl_init(); 
  curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
  curl_setopt($curl,CURLOPT_URL,$link);
  curl_setopt($curl,CURLOPT_HEADER,false);
  curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
  curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
  curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
  $out=curl_exec($curl); 
  $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  curl_close($curl);
  $code=(int)$code;
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
  $tasksFromThisReq = $Response['response']['tasks'];
  array_merge($Tasks,$tasksFromThisReq);
} while(count($tasksFromThisReq));

?>