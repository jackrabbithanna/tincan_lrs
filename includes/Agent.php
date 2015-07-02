<?php


/**
 * The class used for tincan_agent entities
 */
class TincanAgent extends Entity {
  private $notation;
  
  public function __construct($values = array()) {
    parent::__construct($values, 'tincan_agent');
    $this->notation = isset($values['json']) ? $values['json'] : '';
  }
  
	protected function defaultLabel() {
    if(isset($this->name) && $this->name != '') return $this->name;
    return $this->mbox;
  }
  
  function label() {
    return $this->defaultLabel();
  }
  
  protected function defaultUri() {
    return array('path' => 'tincan-agents/' . $this->id);
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
    return _tincan_lrs_basic_json_validation($this->notation,'tincan_agent entity validation');
  }
  
  function populateEntityValues() {
    if($this->notation == '') return FALSE;
    if($this->validateJSON()) {
      //process and populate entity
      $this->populate();
    }
  }
  
  private function createMember($json_array) {
    $values = array();
    $target_id = 0;
    $json = drupal_json_encode($json_array);
    $values['json'] = $json;
   // $values['type'] = 'agent';
      
    $tincan_agent_entity = tincan_lrs_agent_create($values);
    $tincan_agent_entity->populateEntityValues();
    $save_result = $tincan_agent_entity->save();
    if($save_result) $target_id = $tincan_agent_entity->id;
    
    return $target_id;
  }
  
  private function findMember($json_array) {
   
    
    if(!isset($json_array['mbox']) &&
       !isset($json_array['mbox_sha1sum']) &&
       !isset($json_array['openid']) &&
       (!isset($json_array['account']) && !isset($json_array['account']['homePage']) && ! isset($json_array['account']['name'])) ) {
      return 0;  
    }
       
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type','tincan_agent');
    //$query->entityCondition('type','agent');
    
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
  
  private function populate() {
    $json = $this->notation;
    $json_array = drupal_json_decode($json);
    dsm($json_array);
    // objectType
    if(isset($json_array['objectType'])) {
      $this->object_type = $json_array['objectType'];
    }
    else {
      $this->object_type = 'Agent';
    }
    // name
    if(isset($json_array['name'])) {
      $this->name = $json_array['name'];
    }
    // mbox
    if(isset($json_array['mbox'])) {
      $this->mbox= $json_array['mbox'];
    }
    // mbox_sha1sum
    if(isset($json_array['mbox_sha1sum'])) {
      $this->mbox_sha1sum = $json_array['mbox_sha1sum'];
    }
    // openid
    if(isset($json_array['openid'])) {
      $this->openid= $json_array['openid'];
    }
    // account
    if(isset($json_array['account'])) {
      // homepage
      if(isset($json_array['account']['homePage'])) {
        $this->account_home_page = $json_array['account']['homePage'];
      }
      // account name
      if(isset($json_array['account']['name'])) {
        $this->account_name = $json_array['account']['name'];
      }
    }
    
    //members
    // TODO
     if(isset($json_array['member'])) {
       if(!isset($json_array['member'][0]) ) {
         $target_id = $this->findMember($json_array['member']);
         if(!$target_id) {
           $target_id = $this->createMember($json_array['member']);
         }
         if($target_id) {
           $this->tincan_group_members[LANGUAGE_NONE][0]['target_id'] = $target_id;
         }
       }
       else {
         $count  = 0;
         foreach($json_array['member'] as $item) {
           
           $target_id = $this->findMember($item);
           if(!$target_id) {
             $target_id = $this->createMember($item);
           }
           if($target_id) {
             $this->tincan_group_members[LANGUAGE_NONE][$count]['target_id'] = $target_id;
           }
           $count += 1;
         }
       }
     } //end if isset members
     
  } //end populate method
  
}

/**
 * The Controller for TincanAgent entities
 */
class TincanAgentController extends EntityAPIController {
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
    
    if(!isset($values['type'])) {
      $values['type'] = 'agent';
    }
    
    $tincan_agent = parent::create($values);
    return $tincan_agent;
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


class TincanAgentMetadataController extends EntityDefaultMetadataController {
  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
      $info[$this->type]['properties']['id'] = array(
        'label' => t("Drupal Agent ID"), 
        'type' => 'integer', 
        'description' => t("The unique Drupal Agent ID."),
        'schema field' => 'id',
      );
      $info[$this->type]['properties']['object_type'] = array(
        'label' => t("Object Type"), 
        'type' => 'text', 
        'description' => t("Agent Object Type (either Agent or Group)"),
        'schema field' => 'object_type',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['name'] = array(
        'label' => t("Name"), 
        'type' => 'text', 
        'description' => t("Full name of the Agent"),
        'schema field' => 'name',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['mbox'] = array(
        'label' => t("Email IRI"), 
        'type' => 'text', 
        'description' => t("mailto IRI of the Agent"),
        'schema field' => 'mbox',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['mbox_sha1sum'] = array(
        'label' => t("mbox SHA1 hash"), 
        'type' => 'text', 
        'description' => t("The SHA1 hash of a mailto IRI"),
        'schema field' => 'mbox_sha1sum',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['openid'] = array(
        'label' => t("OpenID"), 
        'type' => 'text', 
        'description' => t("OpenID of the Agent"),
        'schema field' => 'openid',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['account_home_page'] = array(
        'label' => t("Account Homepage"), 
        'type' => 'text', 
        'description' => t("The canonical home page for the system the account is on."),
        'schema field' => 'account_home_page',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['account_name'] = array(
        'label' => t("Account UID"), 
        'type' => 'text', 
        'description' => t("The unique id or name used to log in to this account."),
        'schema field' => 'account_home_page',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
      $info[$this->type]['properties']['json'] = array(
        'label' => t("Agent JSON"), 
        'type' => 'text', 
        'description' => t("The JSON representation of the agent"),
        'schema field' => 'json',
        'getter callback' => 'entity_property_verbatim_get',
        'setter callback' => 'entity_property_verbatim_set',
      );
 
    return $info;
  }
}


class TincanAgentUIController extends EntityContentUIController {

   public function hook_menu() {
      $items['tincan-agents/' . '%'] = array(
        'page callback' => 'tincan_lrs_agent_view',
        'page arguments' => array(1),
        'access callback' => 'tincan_lrs_tincan_agent_access',
        'file' => 'Agent.php',
        'file path' => drupal_get_path('module','tincan_lrs') . '/includes',
       // 'type' => MENU_CALLBACK,
      );
     return $items;
  }
  
}

class TincanAgentExtraFieldsController extends EntityDefaultExtraFieldsController {
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

class TincanAgentDefaultViewsController extends EntityDefaultViewsController {
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
function tincan_lrs_agent_view($id) {
  $content = "";
  $entity = entity_load('tincan_agent', array($id));
  
  if (!empty($entity)) {
    $controller = entity_get_controller('tincan_agent');
    $content = $controller->view($entity);
  }
  else {
    $content = '<p>No agent found for drupal id: ' . $id . '</p>';
  }
  
  drupal_set_title($entity[$id]->label());

  return $content;

}