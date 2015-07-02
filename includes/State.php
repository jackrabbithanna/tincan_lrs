<?php


/**
 * The class used for tincan_state entities
 */
class TincanState extends Entity {

  
  public function __construct($values = array()) {
    parent::__construct($values, 'tincan_state');
  }
  
	protected function defaultLabel() {
    return $this->state_id;
  }
  
  function label() {
    return $this->defaultLabel();
  }
  
  protected function defaultUri() {
    return array('path' => 'tincan-states/' . $this->id);
  }
  function toArray() {
     return drupal_json_decode($this->json);
  }
}

/**
 * The Controller for TincanState entities
 */
class TincanStateController extends EntityAPIController {
  public function __construct($entityType) {
    parent::__construct($entityType);
  }
   /* @return
   *   A tincan_state object with all default fields initialized.
   */
  public function create(array $values = array()) {
    // Add values that are specific to our tincan_state
    $values += array( 
      'id' => '',
      'is_new' => TRUE,
    );
    
    $tincan_state = parent::create($values);
    return $tincan_state;
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


class TincanStateMetadataController extends EntityDefaultMetadataController {
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
      $info[$this->type]['properties']['id'] = array(
        'label' => t("Drupal State ID"), 
        'type' => 'integer', 
        'description' => t("The unique Drupal State ID."),
        'schema field' => 'id',
      );
      $info[$this->type]['properties']['state_id'] = array(
        'label' => t("State ID"), 
        'type' => 'text', 
        'description' => t("The unique state id"),
        'schema field' => 'state_id',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['activity_id'] = array(
        'label' => t("Activity ID"), 
        'type' => 'text', 
        'description' => t("Activity id providing context for this state."),
        'schema field' => 'activity_id',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['registration'] = array(
        'label' => t("Registration UUID"), 
        'type' => 'text', 
        'description' => t("The registration id associated with this state."),
        'schema field' => 'registration',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['json'] = array(
        'label' => t("State JSON"), 
        'type' => 'text', 
        'description' => t("The JSON representation of the state"),
        'schema field' => 'json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
 
    return $info;
  }
}


class TincanStateUIController extends EntityContentUIController {

   public function hook_menu() {
      $items['tincan-states/' . '%'] = array(
        'page callback' => 'tincan_lrs_state_view',
        'page arguments' => array(1),
        'access callback' => 'tincan_lrs_tincan_state_access',
        'file' => 'State.php',
        'file path' => drupal_get_path('module','tincan_lrs') . '/includes',
       // 'type' => MENU_CALLBACK,
      );
     return $items;
  }
  
}

class TincanStateExtraFieldsController extends EntityDefaultExtraFieldsController {
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


class TincanStateDefaultViewsController extends EntityDefaultViewsController {
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
 * Loads UI controller and generates view pages for Tincan State entities
 *
 * @param integer id
 *
 * @return string content
 */
function tincan_lrs_state_view($id) {
  $content = "";
  $entity = entity_load('tincan_state', array($id));
  
  if (!empty($entity)) {
    $controller = entity_get_controller('tincan_state');
    $content = $controller->view($entity);
  }
  else {
    $content = '<p>No agent found for drupal id: ' . $id . '</p>';
  }
  
  drupal_set_title($entity[$id]->label());

  return $content;

}