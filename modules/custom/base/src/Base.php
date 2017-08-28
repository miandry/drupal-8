<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\base;

use Drupal\file\Entity\File;
use Drupal\Core\Database\Database;

/**
 * A Base contains all function used .
 */
class Base extends BaseAbstract {

  public $exclude_fields = [];

  public function __construct() {
    
  }

  /**
    param  $query_factory = \Drupal::entityQuery('node');
   *    */
  public function query_init($query_factory) {

    $this->query = $query_factory;
    $this->init();
    return $this->query;
  }

  public function set_exclude_fields($exclude_fields) {
    $this->exclude_fields = $exclude_fields;
  }

  public function add_exclude_field($fields) {
    if (is_array($fields)) {
      $this->exclude_fields = array_merge($this->exclude_fields, $fields);
    }
    else {
      $this->exclude_fields[] = $fields;
    }
  }

  // list of fields no showing in node_load_array
  public function get_exclude_fields() {
    return $this->exclude_fields;
  }

  public function query_execute() {

    return $this->query->execute();
  }

  //query execute for every request
  private function init() {
    $this->query->addTag('node_access');
    $this->query->condition('status', 1);
  }

  /**
   * description load node by view or array;
    param $entity_type = \Drupal::entityTypeManager();
   * $item can be nid or node object
   *    */
  public function node_load_view($entity_type = null, $item, $view_mode = 'teaser') {
    $result = [];
    if ($entity_type == null) {
      $entity_type = \Drupal::entityTypeManager();
      $entity_storage = \Drupal::entityTypeManager()->getStorage('node');
    }
    else {
      $entity_storage = $entity_type->getStorage('node');
    }
    if (is_numeric($item)) {
      $node = $entity_storage->load($item);
    }
    else {
      $node = $item;
    }
    $view_builder = $entity_type->getViewBuilder('node');
    $result['node_view'] = $view_builder->view($node, $view_mode);
    $result['node'] = $node;
    return $result;
  }

  public function node_load_array($node) {
    $fields = array_keys($node->getFields(true));
    $item = [];
    $exclud_fields = $this->get_exclude_fields();
    foreach ($fields as $key => $field) {
      if (!in_array($field, $exclud_fields) && !$node->get($field)->isEmpty()) {

        $field_type = $node->get($field)->getFieldDefinition()->getType(); //->getValue(); 
        $setting_field = $node->get($field)->getFieldDefinition()->getSettings(); //->getValue();

        $bool = true;

        if (isset($field_type)) {
          $type_fun = $field_type;
          if (method_exists($this, $type_fun)) {
            $field_value = $this->{$type_fun}($node, $field);
            $bool = false;
          }
        }
        if (isset($setting_field['target_type'])) {
          $field_target_type = $setting_field['target_type'];

          /// custom field structure  
          $type_fun = $field_type . "_" . $field_target_type;
          if (method_exists($this, $type_fun)) {
            $field_value = $this->{$type_fun}($node, $field);
            $bool = false;
          }
        }
        // custom field structure
        $field_fun = $field;
        if (method_exists($this, $field_fun)) {
          $field_value = $this->{$field_fun}($node, $field);
          $bool = false;
        }
        if ($bool) {
          $field_value = $node->get($field)->getValue();
          if (count($field_value) == 1) {
            $field_value = (array_shift($field_value));
          }
        }
        $item[$field] = ($field_value);
      }
    }
    return $item;
  }
  public  function taxonomy_load_array($taxonomy_term){
    return $this->node_load_array($taxonomy_term);
  }
  //end hook field 
  public function taxonomy_load_multi_by_vid($vid) {
    $connection = Database::getConnection();
    $res = $connection->select('taxonomy_term_data', 'n')
        ->fields('n', array('tid', 'vid'))
        ->condition('n.vid', $vid, '=')
        ->execute()
        ->fetchAllAssoc('tid');
    $items = [];
    foreach (array_keys($res) as $key => $tid) {
      //taxonomy_term
      $taxonomy_term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
      if (is_object($taxonomy_term)) {
        $items[] = array(
          'name' => strtolower($taxonomy_term->label()),
          'tid' => $taxonomy_term->id(),
          'url' => $this->url_taxonomy($tid)
        );
      }
    }
    return $items;
  }

  public function taxonomy_load_by_name($term_name, $vid = null) {
    $taxonomy_terms = taxonomy_term_load_multiple_by_name($term_name, $vid);
    $result = [];
    if (!empty($taxonomy_terms)) {
      foreach ($taxonomy_terms as $key => $taxonomy_term) {
        $result[] = array('name' => $taxonomy_term->label(), 'tid' => $taxonomy_term->id());
      }
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }

  public function taxonomy_load_by_tid($tid) {
    $taxonomy_term = \Drupal::entityTypeManager()
            ->getStorage('taxonomy_term')->load($tid);
    if (is_object($taxonomy_term)) {
      return array('name' => $taxonomy_term->label(), 'tid' => $taxonomy_term->id());
    }
    else {
      return array();
    }
  }

  public function taxonomy_load_full($term_name, $vid = null) {
    $taxonomy_terms = taxonomy_term_load_multiple_by_name($term_name, $vid);
    $result = [];
    if (!empty($taxonomy_terms)) {
      foreach ($taxonomy_terms as $key => $taxonomy_term) {
        $result[] = ($this->node_load_array($taxonomy_term));  
      }
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }

  public function url_taxonomy($term_id) {
    return \Drupal::service('path.alias_manager')->getAliasByPath('/taxonomy/term/' . $term_id);
  }

}
