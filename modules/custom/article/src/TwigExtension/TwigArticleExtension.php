<?php

namespace Drupal\article\TwigExtension;
use Drupal\article\Article;
/**
 * A Extension Twig extension that adds a custom function and a custom filter.
 */
class TwigArticleExtension extends \Twig_Extension {

  /**
   * Generates a list of all Twig functions that this extension defines.
   *
   * @return array
   *   A key/value array that defines custom Twig functions. The key denotes the
   *   function name used in the tag, e.g.:
   *   @code
   *   {{ node_load_twig() }}
   *   @endcode
   *
   *   The value is a standard PHP callback that defines what the function does.
   */
  public function getFunctions() {
    return [
      'block_load_twig' => new \Twig_Function_Function(['Drupal\article\TwigExtension\TwigArticleExtension', 'block_load_twig']),
    ];
  }
  public function getName() {
    return 'article.article_extension';
  }
  public static function block_load_twig($content, $field,$view_mode) {
     $article = new Article(); 
     return $article-> block_load_twig($content, $field,$view_mode);
  }
}
