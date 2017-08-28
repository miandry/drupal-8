<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\base;

use Drupal\file\Entity\File;
use Drupal\Core\Url;
abstract class BaseAbstract {

  public $query;

  public function sort_query($field, $order = 'desc') {
    $this->query->sort($field, $order);
  }

  public function node_type($node) {
    $this->query->condition('type', $node);
  }

  public function pager_query($limit = 12, $order = 0) {
    $this->query->pager($limit, $order);
  }

  public function range_query($start, $limit) {
    $this->query->range($start, $limit);
  }

  public function get_query() {
    return $this->query;
  }

  public function add_filter($field, $value, $operator = '=') {
    $this->query->condition($field, $value, $operator);
  }

  public function add_filter_by_name($field, $value) {
    $this->query->condition($field . '.entity.name', $value);
  }

  public function seach_by_title($keyword, $operator = 'CONTAINS') {
    $this->query->condition('title', $keyword, $operator);
  }

  public function add_filter_multi($filters, $conjunction = 'OR') {
    if ($conjunction == 'AND' || $type == 'and') {
      $group = $this->query->andConditionGroup();
    }
    else {
      $group = $this->query->orConditionGroup();
    }
    foreach ($filters as $key => $filter) {
      if (isset($filter['operator']) && $filter['operator'] != null) {
        $group->condition($filter['field'], $filter['value'], $filter['operator']);
      }
      else {
        //for example $group ->condition('field_tags.entity.name', 'cats');
        $group->condition($filter['field'], $filter['value']);
      }
    }
    $this->query->condition($group);
  }

  public function image_file($node, $field) {
    $image = $node->get($field)->getValue();
    $file = File::load($image[0]['target_id']);
    if (is_object($file)) {
      return ($file->getFileUri());
    }
    else {
      return NULL;
    }
  }

  public function file($node, $field) {
    $file_value = $node->get($field)->getValue();
    $file = File::load($file_value[0]['target_id']);
    if (is_object($file)) {
      return URl::fromUri(file_create_url($file->getFileUri()))->toString();
    }
    else {
      return NULL;
    }
  }
  
  public function string($node, $field){
    $items = $node->get($field)->getValue();
    $result = [];
    foreach ($items as $key => $item) {
       $result[] =$item['value'];
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
    
  }

  public function entity_reference_user($node, $field) {
    $users = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($users as $key => $value) {
      $item_user = \Drupal\user\Entity\User::load($value['target_id']);
      $result[$value['target_id']] = array("user" => $item_user,
        "name" => $item_user->getUsername(),
        "uid" => $value['target_id']);
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }

  public function entity_reference_node($node, $field) {
    $nodes = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($nodes as $key => $value) {
      $node = $entity_type->getStorage('node')->load($value['target_id']);
      $result[$value['target_id']] = array("node" => $node,
        "title" => $node->label(),
        "nid" => $value['target_id']);
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }

  //start hook field 
  public function entity_reference_taxonomy_term($node, $field) {
    $terms = $node->get($field)->getValue();
    $entity_type = \Drupal::entityTypeManager();
    $result = [];
    foreach ($terms as $key => $value) {
      $term = $entity_type->getStorage('taxonomy_term')->load($value['target_id']);
      $result[$value['target_id']] = array("term" => $term,
        "title" => $term->label(),
        "tid" => $value['target_id']);
    }
    if (count($result) == 1) {
      return array_shift($result);
    }
    return $result;
  }

  public function saveFile($fid, $moduleName, $fileType = null) {

    if (isset($fid) && is_string($fid)) {

      /**
       * @var $file File
       */
      $file = File::load($fid);
      // save the file
      /**
       * @var $file_usage DatabaseFileUsageBackend
       */
      $file->setPermanent();
      $file->save();
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, $moduleName, $fileType, $file->id()); // or $themeName

      return true;
    }

    return false;
  }

}
