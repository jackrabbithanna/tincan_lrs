<?php


/**
 * The class used for tincan_statement entities
 */
class TincanStatement extends Entity {
  private $notation;
  private $array;

  
  public function __construct($values = array()) {
    parent::__construct($values, 'tincan_statement');
    $this->notation = isset($values['json']) ? $values['json'] : '';
    $this->statement_id = isset($values['statement_id']) ? $values['statement_id'] : '';
  }
  
	protected function defaultLabel() {
    if(isset($this->statement_id) && $this->statement_id) {
      return $this->statement_id;
    }
    else {
      return $this->id;
    }
  }
  function label() {
    return $this->defaultLabel();
  }
  protected function defaultUri() {
    return array('path' => 'tincan-statements/' . $this->id);
  }
  
  function toArray() {
    return drupal_json_decode($this->notation);
  }
  
  function getJSON() {
    return $this->notation;
  }
  
  function setJSON($json) {
    $this->notation = $json;
    $this->json = $json;
  }
  
  function validateJSON() {
    if($this->notation == '') return FALSE;
    return _tincan_lrs_basic_json_validation($this->notation,'tincan_statement entity validation');
    
  }
  
  function populateEntityValues() {
    if($this->notation == '') return FALSE;
    if($this->validateJSON()) {
    try {
        $this->populate();
    } 
    catch(Exception $e) {
      dsm($e->getMessage());
    }
      
    }
  }
  
  private function findAgent($json_array) {
   
    
    if(!isset($json_array['mbox']) &&
       !isset($json_array['mbox_sha1sum']) &&
       !isset($json_array['openid']) &&
       (!isset($json_array['account']) && !isset($json_array['account']['homePage']) && ! isset($json_array['account']['name'])) ) {
      return 0;  
    }
       
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type','tincan_agent');
    if(isset($json_array['objectType'])) {
      switch($json_array['objectType']) {
        case 'Agent':
          $query->propertyCondition('object_type','Agent');
          break;
        case 'Group':
          $query->propertyCondition('object_type','Group');
          break;
      }
    }
    else {
      $query->propertyCondition('object_type','Agent');
    }
    
    if(isset($json_array['mbox'])) {
      $query->propertyCondition('mbox',$json_array['mbox']);
    }
    if(isset($json_array['mbox_sha1sum'])) {
      $query->propertyCondition('mbox_sha1sum',$json_array['mbox_sha1sum']);
    }
    if(isset($json_array['openid'])) {
      $query->propertyCondition('openid',$json_array['openid']);
    }
    if(isset($json_array['account'])) {
      if(isset($json_array['account']['homePage'])) {
        $query->propertyCondition('account_home_page',$json_array['account']['homePage']);
      }
      if(isset($json_array['account']['name'])) {
        $query->propertyCondition('account_name',$json_array['account']['name']);
      }
    }
    $result = $query->execute();

    if(isset($result['tincan_agent'])) {
      foreach($result['tincan_agent'] as $key => $agent) {
        return $key;
      }
    }
    else return 0;
  }
  
  private function findActivity($json_array) {
     watchdog('findActivity','Query:<pre>%d</pre>',array('%d' =>  print_r($json_array,1)), WATCHDOG_DEBUG);
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type','tincan_activity');
    if(isset($json_array['id'])) {
      $query->propertyCondition('activity_id', $json_array['id']);
    }
    
    $result = $query->execute();

    if(isset($result['tincan_activity'])) {
      foreach($result['tincan_activity'] as $key => $activity) {
        return $key;
      }
    }
    else return 0;
  }
  
  
  private function createAgent($json_array) {
    $values = array();
    $target_id = 0;
    $json = drupal_json_encode($json_array);
    $values['json'] = $json;

    $tincan_agent_entity = tincan_lrs_agent_create($values);
    $tincan_agent_entity->populateEntityValues();
    $save_result = $tincan_agent_entity->save();
    if($save_result) $target_id = $tincan_agent_entity->id;
    
    return $target_id;
  }
  
  private function createActivity($json_array) {
    $values = array();
    $target_id = 0;
    
    $json = drupal_json_encode($json_array);
    $values['json'] = $json;
          
    $tincan_activity_entity = tincan_lrs_activity_create($values);
    $tincan_activity_entity->populateEntityValues();
    $save_result = $tincan_activity_entity->save();
    if($save_result) $target_id = $tincan_activity_entity->id;
    
    return $target_id;
  }
  
  private function populate() {
    $json = $this->notation;
    $json_array = drupal_json_decode($json);
    
   
    dsm($json_array);
    //object_type
    if(isset($json_array['objectType']) && $json_array['objectType']=='SubStatement') {
      $this->object_type = 'SubStatement';
      if(isset($json_array['version'])) unset($json_array['version']);
      if(isset($json_array['id'])) unset($json_array['id']);
      if(isset($json_array['stored'])) unset($json_array['version']);
      if(isset($json_array['authority'])) unset($json_array['version']);
    }
    else {
      $this->object_type = 'Statement';
    }
    //version
    if(isset($json_array['version'])) {
      $this->version = $json_array['version'];
    }
    else {
      if($this->object_type != 'SubStatement') {
        $this->version = '1.0.0';
      }
    }
    //id
    if(isset($json_array['id'])) {
      $this->statement_id = $json_array['id'];
    }
    else { // generate uuid for the statement
      if($this->object_type != 'SubStatement' && $this->statement_id == '') {
        $this->statement_id = uuid_generate();
      }
    }
    
    //timestamp
    // need to do something about saving the datetime values tincan api expects into mysql....
    if(isset($json_array['timestamp'])) {
      $this->timestamp = $json_array['timestamp'];
    }
    // stored
    if(isset($json_array['stored'])) {
      $this->stored = $json_array['stored'];
    }
    else {
      if($this->object_type != 'SubStatement') {
        $this->stored = date('c'); //must be in iso 8601 format;
      }
    }
    // Actor
    if(isset($json_array['actor'])) {
      $this->tincan_actor = array();
      $this->populateActor($json_array['actor']);
    }
    // Verb
    if(isset($json_array['verb'])) {
      $this->tincan_verb = array();
      $this->populateVerb($json_array['verb']);
    }
    
    // Object
    if(isset($json_array['object'])) {
      $this->tincan_object = array();
      $this->populateObject($json_array['object']);
    }
    // Result
   if(isset($json_array['result'])) {
      $this->tincan_result = array();
      $this->populateResult($json_array['result']);
   }
    // Authority
    if(isset($json_array['authority'])) {
      $this->tincan_authority = array();
      $this->populateAuthority($json_array['authority']);
    }
    else {
      //figure out authorize default
      if($this->object_type != 'SubStatement') {
      }
    }
    if(isset($json_array['context'])) {
      $this->tincan_context = array();
      $this->populateContext($json_array['context']);
    }
    
  } //end of populate method
  
  // populate tincan_actor Agent reference field array
  private function populateActor($json_array) {
    $target_id = $this->findAgent($json_array);
    
    if(!$target_id) {
      $target_id = $this->createAgent($json_array);
    }
    if($target_id) $this->tincan_actor[LANGUAGE_NONE][0]['target_id'] = $target_id;
  }
  
  // populate tincan_verb field array
  private function populateVerb($json_array) {
    if(isset($json_array['id'])) {
       $this->tincan_verb[LANGUAGE_NONE][0]['id'] = $json_array['id'];
    }
    if(isset($json_array['display']['en-US'])) { 
      $this->tincan_verb[LANGUAGE_NONE][0]['display_en_us'] = $json_array['display']['en-US'];
    }
    if(isset($json_array['display'])) {
      $this->tincan_verb[LANGUAGE_NONE][0]['display'] = drupal_json_encode($json_array['display']);
    }
 
    $this->tincan_verb[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }
  
  //populate tincan_object field array
  private function populateObject($json_array) {
    $target_id = 0;
    if(isset($json_array['objectType'])) {
       $this->tincan_object[LANGUAGE_NONE][0]['object_type'] = $json_array['objectType'];
       switch($json_array['objectType']) {
         case 'Activity':
           $this->tincan_object[LANGUAGE_NONE][0]['table'] = 'tincan_activity';
           $target_id = $this->findActivity($json_array);
           if(!$target_id) {
             $target_id = $this->createActivity($json_array);
           }
           break;
         case 'Agent':
         case 'Group':
           $this->tincan_object[LANGUAGE_NONE][0]['table'] = 'tincan_agent';
           $target_id = $this->findAgent($json_array);
           if(!$target_id) {
             $target_id = $this->createAgent($json_array);
           } 
           break;
         case 'SubStatement':
           $this->tincan_object[LANGUAGE_NONE][0]['table'] = 'tincan_statement';
           $substatement_json = drupal_json_encode($json_array);
           $tincan_substatement_entity = tincan_lrs_statement_create(array('json' => $substatement_json));
           $tincan_substatement_entity->populateEntityValues();
           $save_result = $tincan_substatement_entity->save();
           if($save_result) $target_id = $tincan_substatement_entity->id;
           break;
         case 'StatementRef':
           $this->tincan_object[LANGUAGE_NONE][0]['table'] = 'statement_reference';
           if(isset($json_array['id'])) {
             $target_id = $json_array['id'];
           }
           break;
       }
    }
    else {
      $target_id = $this->findActivity($json_array);
      if(!$target_id) {
        $target_id = $this->createActivity($json_array);
      }
      $this->tincan_object[LANGUAGE_NONE][0]['object_type'] = 'Activity';
      $this->tincan_object[LANGUAGE_NONE][0]['table'] = 'tincan_activity';
    }

    if($target_id) {
      $this->tincan_object[LANGUAGE_NONE][0]['target_id'] = $target_id;
    }
    $this->tincan_object[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }
  
  //populate tincan_result field array
  private function populateResult($json_array) {
    if(isset($json_array['score'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['score_json'] = drupal_json_encode($json_array['score']);
      
      if(isset($json_array['score']['scaled']) && is_numeric($json_array['score']['scaled'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_scaled'] = $json_array['score']['scaled'];
      }
      if(isset($json_array['score']['raw']) && is_numeric($json_array['score']['raw'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_raw'] = $json_array['score']['raw'];
      }
      if(isset($json_array['score']['min']) && is_numeric($json_array['score']['min'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_min'] = $json_array['score']['min'];
      }
      if(isset($json_array['score']['max']) && is_numeric($json_array['score']['max'])) {
        $this->tincan_result[LANGUAGE_NONE][0]['score_max'] = $json_array['score']['max'];
      }
    }
    if(isset($json_array['success'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['success'] = ($json_array['success'] == 1 || $json_array['success'] == 'true') ? 1 : 0;
    }

    if(isset($json_array['completion'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['completion'] = ($json_array['completion'] == 1 || $json_array['completion'] == 'true') ? 1 : 0;
    }
    if(isset($json_array['response'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['response'] = $json_array['response'];
    }
    if(isset($json_array['duration'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['duration'] = $json_array['duration'];
    }
    if(isset($json_array['extensions'])) {
      $this->tincan_result[LANGUAGE_NONE][0]['extensions'] = drupal_json_encode($json_array['extensions']);
    }
    
    $this->tincan_result[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }
  
  //populate tincan_authority Agent reference field array
  private function populateAuthority($json_array) {
    $target_id = $this->findAgent($json_array);
    
    if(!$target_id) {
      $target_id = $this->createAgent($json_array);
    }
    if($target_id) $this->tincan_authority[LANGUAGE_NONE][0]['target_id'] = $target_id;
  }
  
  //populate tincan_context field array
  private function populateContext($json_array) {
    
    if(isset($json_array['registration'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['registration'] = $json_array['registration'];
    }
    
    if(isset($json_array['revision'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['revision'] = $json_array['revision'];
    }
    
    if(isset($json_array['platform'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['platform'] = $json_array['platform'];
    }
    
    if(isset($json_array['language'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['language'] = $json_array['language'];
    }
    
    if(isset($json_array['statement'])) {
      if(isset($json_array['statement']['objectType'])) {
        $this->tincan_context[LANGUAGE_NONE][0]['statement_reference_object_type'] = $json_array['statement']['objectType'];
      }
      if(isset($json_array['statement']['id'])) {
        $this->tincan_context[LANGUAGE_NONE][0]['statement_reference_statement_id'] = $json_array['statement']['id'];
      }
    }
    
    if(isset($json_array['instructor'])) {
      $target_id = $this->findAgent($json_array);
    
      if(!$target_id) {
        $target_id = $this->createAgent($json_array);
      }
      if($target_id) $this->tincan_instructor[LANGUAGE_NONE][0]['target_id'] = $target_id;
    }
    
    if(isset($json_array['team'])) {
      $target_id = $this->findAgent($json_array);
    
      if(!$target_id) {
        $target_id = $this->createAgent($json_array);
      }
      if($target_id) $this->tincan_team[LANGUAGE_NONE][0]['target_id'] = $target_id;
    }
    
    if(isset($json_array['contextActivities'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['context_activities_json'] = drupal_json_encode($json_array['contextActivities']);
      $context_activities_types = array('parent', 'grouping', 'category', 'other');
      foreach($context_activities_types as $type) {
        if(isset($json_array['contextActivities'][$type]) && is_array($json_array['contextActivities'][$type])) {

          if(!isset($json_array['contextActivities'][$type][0]) ) {
            $target_id = $this->findActivity($json_array['contextActivities'][$type]);
            if(!$target_id) {
              $target_id = $this->createActivity($json_array['contextActivities'][$type]);
            }
            if($target_id) {
              $this->{'tincan_context_activity_' . $type}[LANGUAGE_NONE][0]['target_id'] = $target_id;
            }
          }
          else {
            $count  = 0;
            foreach($json_array['contextActivities'][$type] as $item) {
            
              $target_id = $this->findActivity($item);
              if(!$target_id) {
                $target_id = $this->createActivity($item);
              }
              if($target_id) {
                $this->{'tincan_context_activity_' . $type}[LANGUAGE_NONE][$count]['target_id'] = $target_id;
              }
              $count += 1;
            }
          }
          
        }
      }
    }
    
    if(isset($json_array['extensions'])) {
      $this->tincan_context[LANGUAGE_NONE][0]['extensions'] = drupal_json_encode($json_array['extensions']);
    }
    
    //need context_activities_json
    $this->tincan_context[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array);
  }
  
}

/**
 * The Controller for TincanStatement entities
 */
class TincanStatementController extends EntityAPIController {
  public function __construct($entityType) {
    parent::__construct($entityType);
  }
   /* @return
   *   A tincan_statement object with all default fields initialized.
   */
  public function create(array $values = array()) {
    // Add values that are specific to our tincan_statement
    $values += array( 
      'id' => '',
      'is_new' => TRUE,
      );
    
    $tincan_statement = parent::create($values);
    return $tincan_statement;
  }
 
   /**
   * Implements DrupalEntityControllerInterface::load().
   *
   * @param array $ids
   * @param array $conditions
   *
   * @return array
   */
  public function load($ids = array(), $conditions = array()) {
    $entities = parent::load($ids, $conditions);
    
    foreach($entities as $id => $entity) {
      if(isset($entity->json)) {
        $entities[$id]->setJSON($entity->json);
      }
    }
    
    return $entities;
  }  
  
  /*public function save($entity, DatabaseTransaction $transaction = NULL) {
    $return = parent::save($entity, $transaction);
    return $return;
  }*/
  
  public function buildContent($entity, $view_mode = 'default', $langcode = NULL, $content = array()) {
    
    $build = parent::buildContent($entity,$view_mode,$langcode,$content);
   
    return $build;
  }
  
  
}


class TincanStatementMetadataController extends EntityDefaultMetadataController {
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
      $info[$this->type]['properties']['id'] = array(
        'label' => t("Drupal Statement ID"), 
        'type' => 'integer', 
        'description' => t("The unique Drupal statement ID."),
        'schema field' => 'id',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
  
      $info[$this->type]['properties']['statement_id'] = array(
        'label' => t("Statement ID"), 
        'type' => 'text', 
        'description' => t("The unique AP provided statement ID."),
        'schema field' => 'statement_id',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
  
      $info[$this->type]['properties']['stored'] = array(
        'label' => t("Statement Stored Date"), 
        'type' => 'date', 
        'description' => t("The date the statement was stored"),
        'schema field' => 'stored',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['timestamp'] = array(
        'label' => t("Statement Timestamp"), 
        'type' => 'date', 
        'description' => t("The date when the events described within the statement occurred."),
        'schema field' => 'timestamp',
        // 'getter callback' => '_tincan_lrs_date_getter_callback',
        'getter callback' => 'entity_property_verbatim_get',
        //'setter callback' => '_tincan_lrs_date_setter_callback',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['version'] = array(
        'label' => t("Statement Version"), 
        'type' => 'text', 
        'description' => t("The Version of the statement"),
        'schema field' => 'version',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['object_type'] = array(
        'label' => t("Statement Object Type"), 
        'type' => 'text', 
        'description' => t("Object Type of the statement (statement or substatement"),
        'schema field' => 'object_type',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['json'] = array(
        'label' => t("Statement JSON"), 
        'type' => 'text', 
        'description' => t("The JSON representation of the statement"),
        'schema field' => 'json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      // example of derived value property  -- good for data display -- can't filter on this
   /*   $info[$this->type]['properties']['verb_id'] = array(
        'label' => t("Verb ID"), 
        'type' => 'text', 
        'description' => t("The Verb ID of the statement"),
        'computed' => TRUE,
        'entity views field' => TRUE,
        'queryable' => TRUE,
        //'query callback' => 'entity_metadata_field_query',
        'getter callback' => 'tincan_lrs_verb_id_property_get',
        
      );*/
    return $info;
  }
}


class TincanStatementUIController extends EntityContentUIController {

   public function hook_menu() {
      $items['tincan-statements/' . '%'] = array(
        'page callback' => 'tincan_lrs_statement_view',
        'page arguments' => array(1),
        'access callback' => 'tincan_lrs_tincan_statement_access',
        'file' => 'Statement.php',
        'file path' => drupal_get_path('module','tincan_lrs') . '/includes',
       // 'type' => MENU_CALLBACK,
      );
     return $items;
  }
  
}


class TincanStatementDefaultViewsController extends EntityDefaultViewsController {
   /**
   * Defines the result for hook_views_data().
   */
  public function views_data() {
    $data = parent::views_data();
    //dsm($data);
    $data['tincan_statement']['stored'] = array(
     'title' => t('Stored'),
     'help' => t('Stored date value, the date the statement was stored'),
     'field' => array(
       'handler' => 'tincan_lrs_handler_field_datetime',
       'click sortable' => TRUE,
     ),
     'sort' => array(
       'handler' => 'tincan_lrs_handler_sort_date',
     ),
     'filter' => array(
       'handler' => 'tincan_lrs_handler_filter_datetime',
     ),
     'argument' => array(
       'handler' => 'views_handler_argument_tincanlrs_fulldate',
     ),
   );
   $data['tincan_statement']['timestamp'] = array(
     'title' => t('Timestamp'),
     'help' => t('Timestamp of the statement'),
     'field' => array(
       'handler' => 'tincan_lrs_handler_field_datetime',
       'click sortable' => TRUE,
     ),
     'sort' => array(
       'handler' => 'tincan_lrs_handler_sort_date',
     ),
     'filter' => array(
       'handler' => 'tincan_lrs_handler_filter_datetime',
     ),
     'argument' => array(
       'handler' => 'views_handler_argument_tincanlrs_fulldate',
     ),
   );
    return $data;
  }
  
  function schema_fields() {
    $data = parent::schema_fields();
    
    return $data;
  }
  
  function map_from_schema_info($property_name, $schema_field_info, $property_info) {
    $return = parent::map_from_schema_info($property_name, $schema_field_info, $property_info);
    
    return $return;
  }
  
}

class TincanStatementExtraFieldsController extends EntityDefaultExtraFieldsController {
  protected $propertyInfo;
  
  /**
   * Implements EntityExtraFieldsControllerInterface::fieldExtraFields().
   */
  public function fieldExtraFields() {
    $extra = array();
    $this->propertyInfo = entity_get_property_info($this->entityType);
    if(isset($this->propertyInfo['properties'])) {
      foreach ($this->propertyInfo['properties'] as $name => $property_info) {
        // Skip adding the ID or bundle.
        if ($this->entityInfo['entity keys']['id'] == $name || $this->entityInfo['entity keys']['bundle'] == $name) {
          continue;
        }
        $extra[$this->entityType][$this->entityType]['display'][$name] = $this->generateExtraFieldInfo($name, $property_info);
      }
    }
    // Handle bundle properties.
    $this->propertyInfo += array('bundles' => array());
    if(isset($this->propertyInfo['bundles'])) {
      foreach ($this->propertyInfo['bundles'] as $bundle_name => $info) {
        foreach ($info['properties'] as $name => $property_info) {
          if (empty($property_info['field'])) {
            $extra[$this->entityType][$bundle_name]['display'][$name] = $this->generateExtraFieldInfo($name, $property_info);
          }
        }
      }
    }
   
    return $extra;
  }
}

/**
 * Loads UI controller and generates view pages for Tincan Statements
 *
 * @param integer id
 *
 * @return string content
 */
function tincan_lrs_statement_view($id) {
  $content = "";
  $entity = entity_load('tincan_statement', array($id));
  
  if (!empty($entity)) {
    $controller = entity_get_controller('tincan_statement');
    $content = $controller->view($entity);
  }
  else {
    $content = '<p>No statement found for drupal id: ' . $id . '</p>';
  }
  
  drupal_set_title($entity[$id]->label());

  return $content;

}