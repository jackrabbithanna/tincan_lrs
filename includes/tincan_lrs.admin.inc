<?php

function tincan_lrs_settings_form($form, &$form_state) {
  $form['heading'] = array(
    '#type' => 'markup',
    '#markup' => '<p>General or global settings will be made here</p>',
  );
  $form['#validate'][] = 'tincan_lrs_settings_form_validate';
  return system_settings_form($form);
}

function tincan_lrs_settings_form_validate($form, &$form_state) {

}