<?php

function _tincan_lrs_statements_get_handler_access($op, $args) {
  
  return TRUE;
}

function _tincan_lrs_statements_index_handler($params) {
  watchdog('lrs', "get", array( ), WATCHDOG_DEBUG);
  $statementId = isset($params['statementId']) ? $params['statementId'] : '';
  $statements = _tincan_lrs_statement_get_processor($params);
  return $statements;
}


function _tincan_lrs_statements_get_handler($statementId = '', $params) {
  watchdog('lrs', "get", array( ), WATCHDOG_DEBUG);

  if($statementId != '') $params = array('statementId' => $statementId);
  
  $statements = _tincan_lrs_statement_get_processor($params);
  
  return $statements;
}

function _tincan_lrs_statements_put_handler_access($op, $args) {
  
  return TRUE;
}

function _tincan_lrs_statements_put_handler($statementId, $params, $data) {
  $entityBody = file_get_contents('php://input',TRUE);
watchdog('lrs', "put data: <pre>%d</pre>", array('%d' => print_r($data,1) ), WATCHDOG_DEBUG);
 
  /*$query = drupal_json_decode(urldecode($entityBody));
  $headers = array();
  foreach($_SERVER as $key => $value) {
    $length = 5;
    //$header = _services_fix_header_key($key, $length);
    $header = $key;
    $headers[$header] = $value;
  }*/
  
  $content = drupal_json_encode($data);
  if(_tincan_lrs_basic_json_validation($content, 'statement put query validation')) {
    $result = _tincan_lrs_statement_put_processor($content);
  }
  else {
    $result = '{"error" : "json validation error"}';
  }
  return $result;
}


function _tincan_lrs_statements_post_handler_access($op, $args) {
  $entityBody = file_get_contents('php://input',TRUE);
  $query = drupal_get_query_array($entityBody);
    
  $auth = isset($query['Authorization']) ? $query['Authorization'] : FALSE;
  
  watchdog('lrs','Authorization: <pre>%d</pre>',array('%d' =>  print_r($auth,1)), WATCHDOG_DEBUG);
  
  return TRUE;
}

function _tincan_lrs_statements_post_handler($data, $method, $params) {

  $entityBody = file_get_contents('php://input',TRUE);

 
  $query = drupal_json_decode(urldecode($entityBody));
  $headers = array();
  foreach($_SERVER as $key => $value) {
    $length = 5;
    //$header = _services_fix_header_key($key, $length);
    $header = $key;
    $headers[$header] = $value;
  }
  watchdog('lrs-services-statement','Headers:<pre>%d</pre>',array('%d' =>  print_r($headers,1)), WATCHDOG_DEBUG);
  watchdog('lrs post handler', 'Post data variable: <pre>%d</pre>', array('%d' => print_r($data,1)), WATCHDOG_DEBUG);
  
  $content_array = array();
  // https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#78-cross-origin-requests
  if(isset($method) && $method != '') {
    $query = drupal_get_query_array(urldecode($entityBody));
    $content = isset($query['content']) ? $query['content'] : FALSE;
    $statementId = isset($query['statementId']) ? $query['statementId'] : FALSE;
    $auth = isset($query['Authorization']) ? $query['Authorization'] : FALSE;
    $xapi_version = isset($query['X-Experience-API-Version']) ? $query['X-Experience-API-Version'] : FALSE;
    $content_type = isset($query['Content-Type']) ? $query['Content-Type'] : FALSE;
    $content = isset($query['content']) ? $query['content'] : FALSE;
    if($content) $content_array = drupal_json_decode($content);
     //now shoot the results off to the handlers for the ?method parameter -- 
    switch ($method) {
      case 'GET':
        $result = _tincan_lrs_statement_get_processor($content, $params, $statementId);
      break;
      case 'PUT':
        $result = _tincan_lrs_statement_put_processor($content, $params, $statementId);
      break;
      case 'POST':
        $result = _tincan_lrs_statement_post_processor($content, $params);
      break;
      case 'DELETE':
       // _tincan_lrs_statement_delete_processor($content);
        $result = "can't delete statements";
      break;
      case 'none':
        $content = drupal_json_encode($data);
        $result = _tincan_lrs_statement_post_processor($content);
  
    } // end switch($method)
  } //end if method
  

return  $result;	
}

