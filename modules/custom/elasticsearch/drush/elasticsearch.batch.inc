<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Drupal\elasticsearch\Elasticsearch;

//Bacth Function for deleting a node
function elasticsearch_index_batch_process($info, $context) {

  // check if numeric
  if (is_numeric($info['item'])) {   
    try {   
            $elasticsearch = new Elasticsearch(); 
            $entity_type = \Drupal::entityTypeManager();
            $entity_storage = $entity_type->getStorage('node');
            $node = $entity_storage->load($info['item']);
         
            $rs =  $elasticsearch->es_node_save($node);
            drush_print("success indexing for {$node->getType()}  nid  {$info['item']}") ;
    }
    catch (\Exception $e) {
      $error_message = $e->getMessage();
      drush_print("item nid  " . $info['item'] . " Error :" . $error_message);
    }
  }
  else {
    drush_print("item nid  " . $info['item'] . " is not numeric ");
  }
}
