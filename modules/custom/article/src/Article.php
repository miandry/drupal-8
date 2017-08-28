<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\article;

use Drupal\base\Base;
use Drupal\Core\Database\Database;

class Article extends Base {

  public function block_load_twig($content, $field, $view_mode) {
    $articles = $content[$field];
    $items = [];
    foreach ($articles as $key => $item) {
      if (is_numeric($key)) {
        $items[] = $this->node_load_view(null, $item['#options']['entity'], $view_mode)['node_view'];
      }
    }
    return $items;
  }
  
  
 
  
 
}
