<?php

namespace Drupal\article\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\article\Article;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $count) {
    $results = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));
      // @todo: Apply logic for generating results based on typed_string and other
      // arguments passed.
      $articles = new Article();
      $query_factory = \Drupal::entityQuery('node');
      $articles->query_init($query_factory);
      $articles->node_type('article');
      $articles->add_filter('title', '%'.$typed_string . '%', 'like');
      $articles->range_query(0, $count);
      $resultat_query = $articles->query_execute();
      $entity_type = \Drupal::entityTypeManager();
      $results = [];
      foreach ($resultat_query as $key => $item) {
        
          $node = $entity_type->getStorage('node')->load($item);
          $results[] = [
            "value" => $node->label(). '(' .$item . ')',
            "label" => $node->label(),
           ];
      }
    }

    return new JsonResponse($results);
  }

}
