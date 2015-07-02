<?php

/**
 * A handler to provide a field that is completely custom by the administrator.
 *
 * @ingroup views_field_handlers
 */
 class views_handler_verb_id_field extends views_handler_field {
   function query() {
     // do nothing -- to override the parent query.
   }

   function option_definition() {
     $options = parent::option_definition();

     // Override the alter text option to always alter the text.
     $options['alter']['contains']['alter_text'] = array('default' => TRUE);
     return $options;
   }

   function options_form(&$form, &$form_state) {
     parent::options_form($form, $form_state);

     // Remove the checkbox
     unset($form['alter']['alter_text']);
     unset($form['alter']['text']['#dependency']);
     unset($form['alter']['text']['#process']);
   }

   function render($values) {
     // Render your content.
         
     $entity = entity_load('tincan_statement',array($values->id));
     $json_array = drupal_json_decode($entity[$values->id]->json);
     
     if(isset($json_array['verb']['id'])) return $json_array['verb']['id'];
     else return NULL;
     
   }
}
