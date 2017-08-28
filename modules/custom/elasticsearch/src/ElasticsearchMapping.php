<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\elasticsearch;

class ElasticsearchMapping {
  /*   * elas_(entity_type)_(setting)_mapping* */

  public function elas_entity_reference_node_mapping() {
    return array(
      "type" => "nested",
      "properties" => array(
        "nid" => array(
          "type" => "integer",
          "include_in_all" => true
        ),
        "title" => array(
          "type" => "string",
        )
      )
    );
  }

  public function elas_entity_reference_user_mapping() {
    return array(
      "type" => "nested",
      "properties" => array(
        "tid" => array(
          "type" => "integer",
          "include_in_all" => true
        ),
        "name" => array(
          "type" => "string",
          "include_in_all" => true
        ),
      )
    );
  }

  public function elas_entity_reference_taxonomy_term_mapping() {
        return array(
      "type" => "nested",
      "properties" => array(
        "tid" => array(
          "type" => "integer",
          "include_in_all" => true
        ),
        "name" => array(
          "type" => "string",
          "include_in_all" => true
        ),
      )
    );
  }

  public function elas_entity_reference_node_type_mapping() {
        return array(
      "type" => "nested",
      "properties" => array(
        "tid" => array(
          "type" => "integer",
          "include_in_all" => true
        ),
        "name" => array(
          "type" => "string",
          "include_in_all" => true
        ),
      )
    );
  }

  public function elas_entity_reference_mapping() {
        return array(
      "type" => "nested",
      "properties" => array(
        "tid" => array(
          "type" => "integer",
          "include_in_all" => true
        ),
        "name" => array(
          "type" => "string",
          "include_in_all" => true
        ),
      )
    );
  }

  public function elas_integer_mapping() {
    return array(
      'type' => 'integer',
    );
  }

  public function elas_boolean_mapping() {
    return array(
      'type' => 'string',
    );
  }

  public function elas_created_mapping() {
    return array(
      'type' => 'string',
    );
  }

  public function elas_changed_mapping() {
    return array(
      'type' => 'string',
    );
  }

  public function elas_string_long_mapping() {
    return array(
      'type' => 'string',
    );
  }

  public function elas_string_mapping() {
    return array(
      'type' => 'string',
    );
  }


  public function elas_text_with_summary_mapping() {
    return array(
      'type' => 'string',
    );
  }
  public function elas_timestamp_mappin(){
    return array(
      'type' => 'string',
    );
  }
  public function elas_mapping_default(){
    return array(
      'type' => 'string',
    );
  }
 

}
