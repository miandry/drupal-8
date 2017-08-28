<?php

namespace Drupal\article\mappings;
use Drupal\article\mappings\ElasticsearchArticleMapping;
use Drupal\article\mappings\ElasticsearchArticleIndexing;

class ElasticsearchArticle extends \Drupal\elasticsearch\ElasticsearchBase {
   

  public $node_type ='article';

  //if you dont want some fields not indexing
  function field_exclude() {
    $field_exclude = array();
//    $field_exclude[] = 'field_article_related_articles';
//    $field_exclude[] = 'field_article_slide_images';

    $this->add_exclude_field($field_exclude);
  }

  function mapping() {
    $this->field_exclude();
    $mapper_class = new ElasticsearchArticleMapping();
    $article_mapping = $this->mapping_root($this->node_type,$mapper_class);
    return $article_mapping;
  }
  function indexing($node) {
    $mapper_class = new ElasticsearchArticleIndexing();
    $this->field_exclude();
    $item = $this->indexing_root($node,$mapper_class);
    return $item;
  }

}
