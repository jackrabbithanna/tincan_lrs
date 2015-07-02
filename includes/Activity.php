<?php

/**
 * The class used for tincan_agent entities
 */
class TincanActivity extends Entity {
  private $notation;
  
  public function __construct($values = array()) {
    parent::__construct($values, 'tincan_activity');
    $this->notation = isset($values['json']) ? $values['json'] : '';
  }
  
	protected function defaultLabel() {
    if(isset($this->name_en_us) && $this->name_en_us != '') return $this->name_en_us;
    else return $this->activity_id;
  }
  
  function label() {
    return $this->defaultLabel();
  }
  
  protected function defaultUri() {
    return array('path' => 'tincan-activities/' . $this->id);
  }
  
  function toArray() {
     return drupal_json_decode($this->json);
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
    return _tincan_lrs_basic_json_validation($this->notation, 'tincan_activity entity validation');
  }
  
  function populateEntityValues() {
    if($this->notation == '') return FALSE;
    if($this->validateJSON()) {
      //process and populate entity
      $this->populate();
    }
  }
  private function populate() {
    $json = $this->notation;
    $json_array = drupal_json_decode($json);
    dsm($json_array);
    // Object Type
    if(isset($json_array['objectType'])) {
      if($json_array['objectType'] == 'Activity') {
      
      }
      else {
        // some kind of validation error
      }
    }
    else {
      $this->object_type = 'Activity';
    }
    
    // activity_id
    if(isset($json_array['id'])) {
      $this->activity_id = $json_array['id'];
    }
    
    // Definition
    if(isset($json_array['definition'])) {
      // Name 
      if(isset($json_array['definition']['name'])) {
        // full name language map as json
        $this->name_json = drupal_json_encode($json_array['definition']['name']);
        // US English name
        if(isset($json_array['definition']['name']['en-US'])) {
          $this->name_en_us = $json_array['definition']['name']['en-US'];
        }
      }
      // Description
      if(isset($json_array['definition']['description'])) {
        // full description language map as json
        $this->description_json = drupal_json_encode($json_array['definition']['description']);
        // US English description
        if(isset($json_array['definition']['description']['en-US'])) {
          $this->description_en_us = $json_array['definition']['description']['en-US'];
        }
      }
      
      // type
      if(isset($json_array['definition']['type'])) {
        $this->type = $json_array['definition']['type'];
      }
      
      // more_info
      if(isset($json_array['definition']['moreInfo'])) {
        $this->more_info = $json_array['definition']['moreInfo'];
      }
      // extensions
      if(isset($json_array['definition']['extensions'])) {
        $this->type = drupal_json_encode($json_array['definition']['extensions']);
      }
      
      //interaction_type
      if(isset($json_array['definition']['interactionType'])) {
        $this->interaction_type = $json_array['definition']['interactionType'];
      }
      
      // correct_responses_pattern
      if(isset($json_array['definition']['correctResponsesPattern'])) {
        $this->correct_responses_pattern = drupal_json_encode($json_array['definition']['correctResponsesPattern']);
      }
      
      //Next the interaction fields
      $this->interaction_components_json = '';
      $intact_type = array('choices','scale', 'source','steps', 'target');
      foreach($intact_type as $type) {
        if(isset($json_array['definition'][$type]) && is_array($json_array['definition'][$type])) {
          if(!isset($json_array['definition'][$type][0])) {
            $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][0]['json'] = drupal_json_encode($json_array['definition'][$type]);
            $this->interaction_components_json .= ' ' . drupal_json_encode($item) . ' ';
            if(isset($json_array['definition'][$type]['id'])){
              $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][0]['id'] = $json_array['definition'][$type]['id'];
            }
            if(isset($json_array['definition'][$type]['description'])) {
              $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][0]['description'] = drupal_json_encode($json_array['definition'][$type]['description']);
            }
            if(isset($json_array['definition'][$type]['description']['en-US'])) {
              $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][0]['description_en_us'] = $json_array['definition'][$type]['description']['en-US'];
            }
          }
          else
            $count = 0;
            foreach($json_array['definition'][$type] as $key => $item) {
              $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][$count]['json'] = drupal_json_encode($item);
              $this->interaction_components_json .= ' ' . drupal_json_encode($item) . ' ';
              if(isset($item['id'])){
                $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][$count]['id'] = $item['id'];
              }
              if(isset($item['description'])) {
                $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][$count]['description'] = drupal_json_encode($item['description']);
              }
              if(isset($item['description']['en-US'])) {
                $this->{'tincan_interaction_com_' . $type}[LANGUAGE_NONE][$count]['description_en_us'] = $item['description']['en-US'];
              }
              $count += 1;
            } //end else
          } //end if isset(type)
        } // end foreach type
    } //end if isset(definition)
    
  } //end populate() method
  
}

/**
 * The Controller for TincanAgent entities
 */
class TincanActivityController extends EntityAPIController {
  public function __construct($entityType) {
    parent::__construct($entityType);
  }
   /* @return
   *   A tincan_agent object with all default fields initialized.
   */
  public function create(array $values = array()) {
    // Add values that are specific to our tincan_statement
    $values += array( 
      'id' => '',
      'is_new' => TRUE,
    );
    
    $tincan_activity = parent::create($values);
    return $tincan_activity;
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
    
    /*foreach($entities as $id => $entity) {
      if(isset($entity->json)) {
        $entities[$id]->json_array = drupal_json_decode($entity->json);
      }
    }*/
    
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


class TincanActivityMetadataController extends EntityDefaultMetadataController {
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
      $info[$this->type]['properties']['id'] = array(
        'label' => t("Drupal Agent ID"), 
        'type' => 'integer', 
        'description' => t("The unique Drupal Agent ID."),
        'schema field' => 'id',
      );
      $info[$this->type]['properties']['activity_id'] = array(
        'label' => t("Activity ID"), 
        'type' => 'text', 
        'description' => t("An identifier for a single unique Activity"),
        'schema field' => 'activity_id',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['object_type'] = array(
        'label' => t("Object Type"), 
        'type' => 'text', 
        'description' => t("MUST be Activity when present"),
        'schema field' => 'object_type',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['name_en_us'] = array(
        'label' => t("Name"), 
        'type' => 'text', 
        'description' => t("American English Activity name"),
        'schema field' => 'name_en_us',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['description_en_us'] = array(
        'label' => t("Description"), 
        'type' => 'text', 
        'description' => t("American English Activity description"),
        'schema field' => 'description_en_us',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['type'] = array(
        'label' => t("Type"), 
        'type' => 'text', 
        'description' => t("The type of Activity (IRI)"),
        'schema field' => 'type',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['more_info'] = array(
        'label' => t("More Info"), 
        'type' => 'text', 
        'description' => t("(IRL) Resolves to a document with human-readable information about the Activity."),
        'schema field' => 'more_info',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['interaction_type'] = array(
        'label' => t("Interaction Type"), 
        'type' => 'text', 
        'description' => t("The interaction type"),
        'schema field' => 'interaction_type',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['correct_responses_pattern'] = array(
        'label' => t("Correct responses pattern"), 
        'type' => 'text', 
        'description' => t("The pattern of correct responses"),
        'schema field' => 'correct_responses_pattern',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['interaction_components_json'] = array(
        'label' => t("Interaction Components JSON"), 
        'type' => 'text', 
        'description' => t("Interaction Components JSON"),
        'schema field' => 'interaction_components_json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['name_json'] = array(
        'label' => t("Name Language Map"), 
        'type' => 'text', 
        'description' => t("The Activity Name JSON language map for this activity."),
        'schema field' => 'name_json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['description_json'] = array(
        'label' => t("Description Language Map"), 
        'type' => 'text', 
        'description' => t("The Activity Description JSON language map for this activity."),
        'schema field' => 'description_json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['definition_json'] = array(
        'label' => t("Definition JSON"), 
        'type' => 'text', 
        'description' => t("The Definition JSON data for this activity."),
        'schema field' => 'definition_json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['json'] = array(
        'label' => t("Activity JSON"), 
        'type' => 'text', 
        'description' => t("The JSON representation of the activity"),
        'schema field' => 'json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
 
    return $info;
  }
}


class TincanActivityUIController extends EntityContentUIController {

   public function hook_menu() {
      $items['tincan-activities/' . '%'] = array(
        'page callback' => 'tincan_lrs_activity_view',
        'page arguments' => array(1),
        'access callback' => 'tincan_lrs_tincan_activity_access',
        'file' => 'Agent.php',
        'file path' => drupal_get_path('module','tincan_lrs') . '/includes',
       // 'type' => MENU_CALLBACK,
      );
     return $items;
  }
  
}

class TincanActivityExtraFieldsController extends EntityDefaultExtraFieldsController {
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


class TincanActivityDefaultViewsController extends EntityDefaultViewsController {
   /**
   * Defines the result for hook_views_data().
   */
  public function views_data() {
    $data = parent::views_data();

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

/**
 * Loads UI controller and generates view pages for Tincan Agents
 *
 * @param integer id
 *
 * @return string content
 */
function tincan_lrs_activity_view($id) {
  $content = "";
  $entity = entity_load('tincan_activity', array($id));
  
  if (!empty($entity)) {
    $controller = entity_get_controller('tincan_activity');
    $content = $controller->view($entity);
  }
  else {
    $content = '<p>No agent found for drupal id: ' . $id . '</p>';
  }
  
  drupal_set_title($entity[$id]->label());

  return $content;

}