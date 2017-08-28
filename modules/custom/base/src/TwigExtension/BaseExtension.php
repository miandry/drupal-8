<?php

namespace Drupal\base\TwigExtension;
use Drupal\base\BaseTwigExtension;

/**
 * A Extension Twig extension that adds a custom function and a custom filter .
 */
class BaseExtension extends \Twig_Extension {

  /**
   * Generates a list of all Twig functions that this extension defines.
   *
   * @return array
   *   A key/value array that defines custom Twig functions. The key denotes the
   *   function name used in the tag, e.g.:
   *   @code
   *   {{ node_load_twig() }}
   *   @endcode
   */
  public function getFunctions() {
    return [
      'node_load_twig' => new \Twig_Function_Function(['Drupal\base\TwigExtension\BaseExtension', 'nodeLoadTwig']),
    ];
  }
  public function getName() {
    return 'base.base_extension';
  }
  public static function nodeLoadTwig($node) {
    $twig_base = new BaseTwigExtension();
    return $twig_base->node_load_array($node);
  }


}
