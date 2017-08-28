<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\elasticsearch\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ElasticsearchConfigForm extends ConfigFormBase {

  /**

   * {@inheritdoc}

   */
  public function getFormId() {

    return 'elasticsearch_config_form';
  }

  /**

   * {@inheritdoc}

   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);

    $config = $this->config('elasticsearch.settings');
  $str= "<h4>NOTICE FOR ELASTICSEARCH :</h4>";
  $str=$str."<b>Drush Command-1 : </b> <span style='color:#0074BD'> drush elas-create </span>  ( create mapping following nodes : 'article,event,listing,..')<br/>" ;
  $str=$str."<b>Drush Command-2 : </b> <span style='color:#0074BD'> drush elas-delete </span>  ( delete mapping following nodes : 'article,event,listing,..')<br/>" ;
  $str=$str."<b>Drush Command-3  : </b> <span style='color:#0074BD'> drush elas-indexing </span>  ( create indexing following nodes : 'article,event,listing ..')<br/>" ;
 
 
  $str=$str.'<br/>Please enter elastic server url for example <span style="color:#0074BD"> http://192.168.1.3:9200/ </span>';

  $form['notes'] = array(
    '#markup' => $this->t($str),
  );
    $form['host'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#default_value' => $config->get('elasticsearch.host'),
      '#required' => TRUE,
    );
    $form['index'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Index Name'),
      '#default_value' => $config->get('elasticsearch.index'),
      '#required' => TRUE,
    );

//    $node_types = \Drupal\node\Entity\NodeType::loadMultiple();
//
//    $node_type_titles = array();
//
//    foreach ($node_types as $machine_name => $val) {
//      $node_type_titles[$machine_name] = $val->label();
//    }
//    kint($node_type_titles);
//
    $form['node_types'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Indexing Node Types '),
      '#default_value' => $config->get('elasticsearch.node_types'),
    );

    return $form;
  }

  /**

   * {@inheritdoc}

   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('elasticsearch.settings');

    $config->set('elasticsearch.host', $form_state->getValue('host'));

    $config->set('elasticsearch.index', $form_state->getValue('index'));
    $config->set('elasticsearch.node_types', $form_state->getValue('node_types'));

    $config->save();

    return parent::submitForm($form, $form_state);
  }

  /**

   * {@inheritdoc}

   */
  protected function getEditableConfigNames() {

    return [

      'elasticsearch.settings',
    ];
  }

}
