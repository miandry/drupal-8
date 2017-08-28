<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\elasticsearch;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Component\Serialization\Json;

class ElasticsearchBase {

  private $es_host;
  public $es_query;
  public $document;
  public $index = "miandry";
  public $database;
  public $exclude_fields = ['uuid', 'langcode', 'revision_timestamp',
    'revision_uid', 'revision_log', 'revision_translation_affected'
    , 'default_langcode', 'moderation_state', 'status', 'comment', 'field_image', 'path', 'vid'
    , 'type', 'uid',
  ];

  function __construct() {
    $config = \Drupal::config('elasticsearch.settings');
    $this->es_host = $config->get('elasticsearch.host');
    $this->index = $config->get('elasticsearch.index');
    $this->database = \Drupal::database();
  }

  static function getInstance() {
    global $es_api;
    if ($es_api instanceof ElasticsearchBase) {
      return $es_api;
    }
    else {
      $es_api = new ElasticsearchBase();
      return $es_api;
    }
  }

  public function getIndex() {
    $config = \Drupal::config('elasticsearch.settings');
    return $this->index = $config->get('elasticsearch.index');
  }

  public function get($uri = '') {
//headers
    $headers = array();
    //$headers = array("username:miandry","password:miandry",); example
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->es_host . $uri);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_error($curl)) {
      error_log('error:' . curl_error($curl));
    }
    curl_close($curl);

    return $response;
  }

  public function put($uri, $data) {
//headers
    $headers = array();
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->es_host . $uri);
    curl_setopt($curl, CURLOPT_HEADER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_error($curl)) {
      error_log('error:' . curl_error($curl) . $response);
    }
    curl_close($curl);

    return $response;
  }

  public function post($uri, $data) {
//headers

    $headers = array();
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->es_host . $uri);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_error($curl)) {
      error_log('error:' . curl_error($curl) . $response);
    }
    curl_close($curl);

    return $response;
  }

  public function delete($uri) {
//headers
    $headers = array();
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->es_host . $uri);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

    $response = curl_exec($curl);
    $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if (curl_error($curl)) {
      error_log('error:' . curl_error($curl) . $response);
    }
    curl_close($curl);

    return $response;
  }

  public function insert($nid, $_id, $type) {
     
    $return_value = NULL;
    try {
      $return_value = $this->database->insert('elasticsearch')
          ->fields(array("nid" => $nid, "_id" => $_id, "type" => $type))
          ->execute();
    }
    catch (Exception $e) {
      drupal_set_message(t('db_insert failed. Message = %message, query= %query', array('%message' => $e->getMessage(), '%query' => $e->query_string)), 'error');
    }
    return $return_value;
  }

  function update($nid, $_id) {
    try {
      $count = $this->database->update('elasticsearch')
          ->fields(array("_id" => $_id))
          ->condition('nid', $nid)
          ->execute();
    }
    catch (Exception $e) {
      drupal_set_message(t('db_update failed. Message = %message, query= %query', array('%message' => $e->getMessage(), '%query' => $e->query_string)), 'error');
    }
    return $count;
  }

//
  public function load_by($field_name, $field) {
    return $this->database->select('elasticsearch', 'n')
            ->fields('n', array('nid', '_id', 'type'))
            ->condition($field_name, $field, '=')
            ->range(0, 1)
            ->execute()
            ->fetchObject();
  }
  public function delete_by_nid($nid) {
    $this->database->delete('elasticsearch')
        ->condition('nid', $nid)
        ->execute();
  }

  public function delete_by__id($_id) {
   $this->database->delete('elasticsearch')
        ->condition('_id', $_id)
        ->execute();
  }

//
  public function _json_encode_string($encode) {

    $needle = array();
    $replace = array();

    $needle[] = "'";
    $replace[] = '\u0027';

    $needle[] = '"';
    $replace[] = '\u0022';


    $needle[] = '&';
    $replace[] = '\u0026';



    $needle[] = '<';
    $needle[] = '>';
    $replace[] = '\u003C';
    $replace[] = '\u003E';



    $encode = str_replace($replace, $needle, $encode);

    return $encode;
  }

  function condition($field, $value) {
    
  }

  function execute() {
    $document = $this->document;
    $index = $this->index;
    if (!empty($document)) {
      $uri = "/" . $index . "/$document/_search";
    }
    else {
      $uri = "/" . $index . "/_search";
    }
    $json_format = $this->_json_encode_string(Json::encode($this->es_query));
    $rs = Json::decode($this->post($uri, $json_format));
    return ($rs['hits']['hits']);
  }

  function node_type($type) {
    $this->document = $type;
  }

  function pager($limit = 12, $order = 0) {
    $param = \Drupal::request()->query->all();
    $this->es_query["size"] = $limit;
    if (isset($param['page'])) {
      $this->es_query["from"] = floatval($param['page']) * floatval($this->es_query["size"]);
    }
    else {
      $this->es_query["from"] = $order;
    }
  }

  function sort($field, $order) {
    
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

  public function get_exclude_fields() {
    return $this->exclude_fields;
  }

  public function mapping_root($node_type, $mapper_class) {
    $mapping = [];

    $field_exclude = $this->get_exclude_fields();

    $fields_node_config = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $node_type);
    $fields_node = (array_keys($fields_node_config));
//    if (is_object($node)) {
    foreach ($fields_node as $key => $field) {
      if (!in_array($field, $field_exclude)) {
        $field_type = $fields_node_config[$field]->getType();
        $setting_field = $fields_node_config[$field]->getSettings();
        $bool =true;
        ///hook by type field
        if (isset($field_type)) {
          $type_fun = "elas_" . $field_type . "_mapping";
          if (method_exists($mapper_class, $type_fun)) {
            $mapping[$field] = $mapper_class->{$type_fun}();
            $bool =false;
          }
        }
        /// hook type and target type
        if (isset($setting_field['target_type'])) {
          $type_fun = "elas_" . $field_type . "_" . $setting_field['target_type'] . "_mapping";
          if (method_exists($mapper_class, $type_fun)) {
            $mapping[$field] = $mapper_class->{$type_fun}();
             $bool =false;
          }
        }
        
        // hook  custom field structure
        $field_fun = "elas_" . $field . "_mapping";
        if (method_exists($this, $field_fun)) {
          $mapping[$field] = $mapper_class->{$field_fun}();
           $bool =false;
        }
        
        //if any hook mapping  is found
        if($bool){
        $mapping[$field]= $mapper_class->elas_mapping_default();       
        }
        
      }
    }


    return $mapping;
  }

  public function indexing_root($node, $mapper_class) {
    $fields = array_keys($node->getFields(true));


    $item = [];

    $exclud_fields = $this->get_exclude_fields();

    foreach ($fields as $key => $field) {
      // var_dump($field);
      // var_dump($node->get($field)->isEmpty());
      if (!in_array($field, $exclud_fields)) {
        
        // var_dump($field);
        $field_type = $node->get($field)->getFieldDefinition()->getType(); //->getValue(); 
        $setting_field = $node->get($field)->getFieldDefinition()->getSettings(); //->getValue();

        $bool=true;
        // hook  type structure
        $type_only_fun = "elas_" . $field_type . "_indexing";
        if (method_exists($mapper_class, $type_only_fun)) {
          $field_value = $mapper_class->{$type_only_fun}($node, $field);
          $bool=false;
        }
        if (isset($setting_field['target_type'])) {
          $field_target_type = $setting_field['target_type'];
          $field_value = $node->get($field)->getValue();
          /// custom field and setting structure  
          $type_fun = "elas_" . $field_type . "_" . $field_target_type . "_indexing";
          if (method_exists($mapper_class, $type_fun)) {
            $field_value = $mapper_class->{$type_fun}($node, $field);
            $bool=false;
          }
        }
        // custom field structure
        $field_fun = "elas_" . $field . "_indexing";
        if (method_exists($mapper_class, $field_fun)) {
          $field_value = $mapper_class->{$field_fun}($node, $field);
          $bool=false;
        }
        if($bool){
          $field_value = $node->get($field)->value;
        }
        $item[$field] = ($field_value);
      }
     
  

    }

    return $item;
  }

}
