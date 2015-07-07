<?php
/*Agents API */

function _tincan_lrs_agents_index_handler_access() {
  return true;
}

function _tincan_lrs_agents_index_handler($params) {
 $result = _tincan_lrs_agents_get_processor($params);
 
 //here we want to return an agent object
 return $result;
}

function _tincan_lrs_agents_get_handler_access() {
  return true;
}

function _tincan_lrs_agents_get_handler($agentId,  $params) {
  switch($agentId) {
    case 'profile':
      $result = _tincan_lrs_agent_profiles_get_processor($params);
      break;
    default:
      $result = _tincan_lrs_agents_get_processor($params);
  
  }
  return $result;
  //here we want to return an agent object
}

function _tincan_lrs_agents_put_handler_access($op, $args) {
  return TRUE;
}

function _tincan_lrs_agents_put_handler($type, $params, $data) {
  $content = drupal_json_encode($data);
  
  /*
    when url parameters are given the $param array is populated with values
    when parameters are set via the Parameters in Poster, it is not populated 
   */
  if($type == 'profile') {
    if(_tincan_lrs_basic_json_validation($content, 'agent profile put query validation')) {
      $result = _tincan_lrs_agent_profiles_put_processor($params, $content);
    }
    else {
      $result = '{"error" : "json validation error"}';
    }
  }
  
  else $result = 'no putting on agents!';
  return $result;
}

function _tincan_lrs_agents_post_handler_access($op, $args) {
//  watchdog('lrs-state-post-access', "args: %d", array('%d' => print_r($args,1) ), WATCHDOG_DEBUG);
  return TRUE;
}

function _tincan_lrs_agents_post_handler($data, $params, $type) {
  $entityBody = file_get_contents('php://input',TRUE);

 /*
    when url parameters are given the $param array is populated with values
    when parameters are set via the Parameters in Poster, it is not populated 
   */
  $query = drupal_json_decode(urldecode($entityBody));
  $headers = array();
  foreach($_SERVER as $key => $value) {
    $length = 5;
    //$header = _services_fix_header_key($key, $length);
    $header = $key;
    $headers[$header] = $value;
  }
  watchdog('lrs-services-statement','Headers:<pre>%d</pre>',array('%d' =>  print_r($headers,1)), WATCHDOG_DEBUG);
  watchdog('lrs activities post handler', 'Post data variable: <pre>%d</pre>', array('%d' => print_r($data,1)), WATCHDOG_DEBUG);
  
  $content_array = array();
  // https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#78-cross-origin-requests
  if(isset($params['method']) && $params['method'] != '') {
    $query = drupal_get_query_array(urldecode($entityBody));
    $content = isset($query['content']) ? $query['content'] : FALSE;
    $profileId = isset($query['profileId']) ? $query['profileId'] : FALSE;
    $auth = isset($query['Authorization']) ? $query['Authorization'] : FALSE;
    $xapi_version = isset($query['X-Experience-API-Version']) ? $query['X-Experience-API-Version'] : FALSE;
    $content_type = isset($query['Content-Type']) ? $query['Content-Type'] : FALSE;
    $content = isset($query['content']) ? $query['content'] : FALSE;
    if($content) $content_array = drupal_json_decode($content);
    
     //now shoot the results off to the handlers for the ?method parameter --
      if($type == 'profile') {
        switch ($params['method']) {
          case 'GET':
            $result = _tincan_lrs_agent_profiles_get_processor($query, $content,$profileId);
          break;
          case 'PUT':
            $result = _tincan_lrs_agent_profiles_put_processor($query, $content, $profileId);
          break;
          case 'POST':
            $result = _tincan_lrs_agent_profiles_post_processor($query, $content);
          break;
          case 'DELETE':
            $result = _tincan_lrs_agent_profiles_delete_processor($query, $content, $profileId);
          break;
        } // end switch($method)
      } //end if type = profile
      else {
        $result = "can't post agents";
      } 
  } //end if method
  else {
    if($type == 'profile') {
       $content = drupal_json_encode($data);
       $result = _tincan_lrs_agent_profiles_post_processor($params, $content);
    }
    else $result = "can't post agents";
  }

  return  $result;	
}

function _tincan_lrs_agents_delete_handler_access($op, $args) {
  return TRUE;
}

function _tincan_lrs_agents_delete_handler($type, $params, $data) {
  if($type == 'profile') {
    $result = _tincan_lrs_agent_profiles_delete_processor($params,$data);
  }
  else {
    $result = 'no deleting agents!';
  }
  return $result;
}