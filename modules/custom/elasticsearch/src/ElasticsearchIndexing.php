<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\elasticsearch;

class ElasticsearchIndexing {

  /** hook field type entity reference user * */
  public function elas_entity_reference_user_indexing($node, $field) {
    $users = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($users as $key => $value) {
      $item_user = \Drupal\user\Entity\User::load($value['target_id']);
      $result[] = array(
        "name" => $item_user->getUsername(),
        "uid" => $value['target_id']);
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }
  /** hook field type entity reference node * */
  public function elas_entity_reference_node_indexing($node, $field) {
    $nodes = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($nodes as $key => $value) {
      $node = $entity_type->getStorage('node')->load($value['target_id']);
      $result[] = array(
        "title" => $node->label(),
        "nid" => $value['target_id']);
    }
  
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }
    /** hook field type entity reference node * */
  public function elas_entity_reference_node_type_indexing($node, $field) {
    $nodes = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($nodes as $key => $value) {
      $node = $entity_type->getStorage('node')->load($value['target_id']);
      $result[] = array(
        "title" => $node->label(),
        "nid" => $value['target_id']);
    }
  
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }
  public function elas_entity_reference_taxonomy_term_indexing($node, $field) {
    $terms = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($terms as $key => $value) {
      $term = $entity_type->getStorage('taxonomy_term')->load($value['target_id']);
      $result[] = array(
        "title" => $term->label(),
        "tid" => $value['target_id']);
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }
  public function elas_timestamp_indexing($node, $field){
     return $node->get($field)->value;
  }
}
