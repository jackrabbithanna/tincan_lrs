<?php

/*Activities API */
function _tincan_lrs_activities_get_handler_access() {
  return true;
}

function _tincan_lrs_activities_get_handler($activityId) {
  return 'Activities get: ' . $activityId;
  //here we want to return an activity object
}


function _tincan_lrs_activities_post_handler_access($op, $args) {
//  watchdog('lrs-state-post-access', "args: %d", array('%d' => print_r($args,1) ), WATCHDOG_DEBUG);
  return TRUE;
}

function _tincan_lrs_activities_post_handler($activity_type,$method) {
 $entityBody = file_get_contents('php://input',TRUE);
 $query = drupal_get_query_array(urldecode($entityBody));
 
 $agent = isset($query['agent']) ? $query['agent'] : FALSE;
 $activityId= isset($query['activityId']) ? $query['activityId'] : FALSE;
 $stateId= isset($query['stateId']) ? $query['stateId'] : FALSE;
 $auth = isset($query['Authorization']) ? $query['Authorization'] : FALSE;
 $xapi_version = isset($query['X-Experience-API-Version']) ? $query['X-Experience-API-Version'] : FALSE;
 $content_type = isset($query['Content-Type']) ? $query['Content-Type'] : FALSE;
 
 
 if($agent) $agent_array = drupal_json_decode($agent);
 else $agent_array = array();
 
 
 
 watchdog('lrs','State Query:<pre>%d</pre>',array('%d' =>  print_r($query,1)), WATCHDOG_DEBUG);
 watchdog('lrs','Agent:<pre>%d</pre>',array('%d' =>  print_r($agent_array,1)), WATCHDOG_DEBUG);

 return  drupal_json_encode($agent_array);
  
}

function _tincan_lrs_activities_state_get_handler_access() {
  return TRUE;
}

function _tincan_lrs_activities_state_get_handler($stateId) {
  return 'Activities State: ' . $stateId;
}