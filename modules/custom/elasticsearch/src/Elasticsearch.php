<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\elasticsearch;

use Drupal\Component\Serialization\Json;

class Elasticsearch extends ElasticsearchBase {

  function index_mapping() {
    $config = \Drupal::config('elasticsearch.settings');
    return array(
      'node' => explode(',', $config->get('elasticsearch.node_types'))
    );
  }

  function create_index_mapping($type = null) {
    $index = $this->getIndex();
    $m_a = array(
      'settings' => array(
        'number_of_shards' => 1,
      ),
      'mappings' => array()
    );
    $index_mapping = $this->index_mapping();
    $index_types = $index_mapping['node'];

    if ($type == null) {
      foreach ($index_types as $type) {
        $class = "Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type);
        if (class_exists($class)) {
          $els_type = new $class();
          $m_a['mappings'][$type]['properties'] = $els_type->mapping();
          $rs = $this->put($index . '/', Json::encode($m_a));
          if (Json::decode($rs)["error"]["type"] != "index_already_exists_exception") {
            print "\n ";
            print_r($rs);
            print "\n ";
          }
        }
        else {
          print "\n Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type) . " namespace   entity not exist";
        }
      }
    }
    else {
      if (in_array(trim($type), $index_types)) {

        $class = "Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type);
        $els_type = new $class;
        $m_a['mappings'][$type]['properties'] = $els_type->mapping();
        $rs = $this->put($index . '/', Json::encode($m_a));
        if (Json::decode($rs)["error"]["type"] != "index_already_exists_exception") {
          print_r($rs);
        }
      }
      else {
        print $type . " entity not exist";
      }
    }
  }

  function delete_index_mapping_all() {
    $index = $this->getIndex();
    $rs = $this->delete($index . '/');
    $result = Json::decode($rs);
    if ($result["acknowledged"]) {
      $this->database->truncate('elasticsearch')->execute();
    }
    print "/n" . $rs . "/n";
  }

//
//  function update_index_mapping($type) {
//    $index = 'family';
//    $uri = $index . '/' . $type . '/_mapping';
//    include_once "mappings/{$type}.php";
//    $document_s = "{$type}_mapping";
//    $document = $document_s();
//
//    $post = array();
//    $post[$type] = array(
//      'properties' => $document,
//    );
//    $rs = $this->put($uri, drupal_json_encode($post));
//    print $rs;
//  }
//
  function es_node_create($node) {
    if (!is_object($node)) {
      return false;
    }
    $type = $node->getType();
    $class = "Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type);
    if (class_exists($class)) {
      $els_type = new $class();
      $es_node = $els_type->indexing($node);

      $document = $type;
      $index = $this->getIndex();
      $uri = "{$index}/{$document}";
      $rt = $this->post($uri, Json::encode($es_node));
      $response = Json::decode($rt);
      if ($response["created"]) {
        $this->insert($node->get('nid')->value, $response["_id"], $type);
      }
      print $rt . "\n";
    }
    else {
      print "\n Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type) . " namespace   entity not exist";
    }
  }

  function es_node_save($node) {
    if (!is_object($node)) {
      return false;
    }
    $map = $this->load_by("nid", $node->get('nid')->value);
    if (is_object($map)) {
      $rt = $this->es_node_update($node);
    }
    else {
      $rt = $this->es_node_create($node);
    }
    return $rt;
  }

  function es_node_update($node) {
    if (!is_object($node)) {
      return false;
    }
    $type = $node->getType();
    $nid = $node->get('nid')->value;
    $class = "Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type);
    if (class_exists($class)) {
      $els_type = new $class();
      $es_node = $els_type->indexing($node);
      $document = $type;
      $index = $this->getIndex();
      $map = $this->load_by("nid",$nid);

      $uri = "{$index}/{$document}/" . $map->_id;
      $rt = $this->post($uri, Json::encode($es_node));
      $response = Json::decode($rt);
      if ($response["_shards"]["successful"] == 1) {
        $this->update($nid, $response["_id"]);
      }
    }
    else {
      print "\n Drupal\\" . $type . "\mappings\\Elasticsearch" . ucfirst($type) . " namespace   entity not exist";
    }
  }

//
//  function es_node_get($node) {
//    $document = $node->type;
//    $index = 'family';
//    $uri = "{$index}/{$document}/_search";
//    $query['query']['match_all']["nid"] = $node->nid;
//// var_dump(drupal_json_encode($query));die();
//    $rt = drupal_json_decode($this->get($uri, drupal_json_encode($query)));
//    return ($rt);
//  }
//
  function es_node_delete($node) {
    $type = $node->getType();
    $nid = $node->get('nid')->value;
    $map = $this->load_by("nid", $nid);
    if (!is_object($map)) {
      return null;
    }
    $document = $type;
    $index = $this->getIndex();
    $uri = "{$index}/{$document}/{$map->_id}";
    $rt = $this->delete($uri);
    $reponse = Json::decode($rt);
    if ($reponse["found"]) {
      $this->delete_by__id($reponse["_id"]);
    }
    return($rt);
  }
//
//  function es_node_delete_by_type($type = null) {
//    if ($type != null) {
//      $res = $this->load_multi_by('type', $type);
//      if (!empty($res)) {
//        foreach ($res as $key => $value) {
//          $node = node_load($value->nid);
//          $r = $this->es_node_delete($node);
//          if (drupal_json_decode($r)["found"]) {
//            print "deleted indexing  successfull nid " . $value->nid;
//          }
//        }
//      }
//      else {
//        print "all " . $type . " indexing are deleted   already ";
//      }
//    }
//  }
//
  function es_node_list($query, $document = '') {
    $index = $this->getIndex();
    if (!empty($document)) {
      $uri = "/" . $index . "/$document/_search";
    }
    else {
      $uri = "/" . $index . "/_search";
    }
    $json_format = $this->_json_encode_string(Json::encode($query));
    $rs = $this->post($uri, $json_format);
    return Json::decode($rs);
  }
}
