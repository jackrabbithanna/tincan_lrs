<?php


function _tincan_lrs_about_index_handler_access() {
  return TRUE;
}

function _tincan_lrs_about_index_handler($params) {
  $about= array(
    'version' => array(
      '1.0.0',
    ),
    'extensions' => new stdClass(),
  );
  return drupal_json_encode($about);
}