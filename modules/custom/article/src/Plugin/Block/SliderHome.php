<?php

namespace Drupal\article\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\article\Article;

/**
 * @Block(
 *   id = "slider_home",
 *   admin_label = @Translation("Slider home block "),
 *   category = @Translation("slider"),
 * )
 */
class SliderHome extends BlockBase implements ContainerFactoryPluginInterface {

  protected $entity_manager;
  protected $entity_query;
  protected $renderer;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, QueryFactory $entity_query, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entity_manager = $entity_manager;
    $this->entity_query = $entity_query;
    $this->renderer = $renderer;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $configuration, $plugin_id, $plugin_definition, $container->get('entity.manager'), $container->get('entity.query'), $container->get('renderer')
    );
  }

  /**
   * @inheritdoc
   *
   * @return array
   */
  public function defaultConfiguration() {
    return [
         'items' => [],
    ];
  }

  /**
   * @inheritdoc
   *
   * @param array                                $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $items = $this->configuration['items'];
    $str = "<h2> SLIDER HOME </h2>";
    $form['notes'] = [
      '#markup' => $this->t($str),
      '#weight' => -1,
    ];
    for ($i = 1; $i < 6; $i++) {
      $form['item_' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t($i . ' - Article ' ),
        '#autocomplete_route_name' => 'article.autocomplete',
        '#autocomplete_route_parameters' => array('count' => 10),
        '#weight' => $i,
      ];
      if (isset($items['item_' . $i])) {
        $form['item_' . $i]['#default_value'] = $items['item_' . $i]['label'] . "(" . $items['item_' . $i]['nid'] . ")";
      }
    }

    return $form;
  }

  /**
   * @inheritdoc
   *
   * @param array                                $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $article = new Article();
    for ($i = 1; $i < 6; $i++) {
      if ($form_state->getValue('item_' . $i) != "") {
        $string = $form_state->getValue('item_' . $i);
        $string_array = explode('(', $string);
        $nid = (explode(')', $string_array[count($string_array) - 1])[0]);
        unset($string_array[count($string_array) - 1]);
        $title = implode("(", $string_array);
        $this->configuration['items']['item_' . $i] = array(
          'nid' => $nid,
          'label' => $title
        );
      }
      else {
        $this->configuration['items']['item_' . $i] = NULL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $article = new Article();
    $build = [
      '#theme' => 'slider_home',
      '#cache' => [
        'tags'=>['node'], // Invalidate cache whenever a new node is created/modified/deleted
        'contexts'=>['url'] // Invalidate cache per url
      ]
    ];

    $config_items = $this->configuration['items'];
    $items = [];
    //article push slider checked should be more then 5 items
    for ($i = 1; $i < count($config_items)+1; $i++) {
      $nid = $config_items['item_' . $i]['nid'];
      if ($nid) {
        ///load view
        $entity_type = $this->entity_manager;
        $node = $article->node_load_view($entity_type, $nid, 'slider_home');
        $items[$i] = $node['node_view'];
      }
    }
    $build['#items'] = $items;
    return $build;
  }



}
