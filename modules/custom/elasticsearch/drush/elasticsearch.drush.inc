<?php

use Drupal\elasticsearch\Elasticsearch;
use Drupal\Core\Database\Database;

function elasticsearch_drush_command() {
  $items = [];

  $items['elas-mapping-create'] = [
    'description' => "Elasticsearch, to create index mapping",
    'aliases' => ['elas-mapping'],
    'arguments' => ['type' => 'Node type name.'],
  ];
  $items['elas-indexing-delete-all'] = [
    'description' => 'Waringing !!! It will delete all indexed date in Elasticsearch. To delete index mapping',
    'aliases' => ['elas-delete'],
  ];
  $items['elas-indexing'] = [
    'description' => 'Elasticsearch, to create index mapping',
    'arguments' => [
      'type' => 'node type of mapping',
    ]
  ];
  return $items;
}

function drush_elasticsearch_elas_indexing($type = null) {
  $elasticsearch = new Elasticsearch();

  if ($type != null) {
    $nids = \Drupal::database()->query("SELECT nid FROM {node_field_data} where status=1 AND type='{$type}'")->fetchCol();
  }
  else {
    $index_mapping = $elasticsearch->index_mapping();
    $index_types = $index_mapping['node'];
    $where = " type='" . $index_types[0] . "'";
    foreach ($index_types as $k => $value) {
      if ($k != 0)
        $where = $where . " OR type = '" . $value . "'";
    }
    $nids = \Drupal::database()->query("SELECT nid FROM {node_field_data} where status=1 AND {$where} ")->fetchCol();
  }
  $count = 0;
  $indexed = 0;
  foreach ($nids as $key => $value) {
    if (!is_object($elasticsearch->load_by('nid', $value))) {
      $count++;
    }
    else {
      unset($nids[$key]);
      $indexed++;
    }
  }
  drush_print("There are " . $count . " node to indexing and " . $indexed . " indexed");
  drush_print('Data is Ready !! ');
  if (drush_confirm(dt('Do you want to continue the process ?'))) {
    $total = $count;
    $operations = [];
    if ($total > 0) {
      foreach ($nids as $index => $item) {
        $info = [
          'item' => $item,
          'index' => $index,
          'total' => $total,
        ];
        $operations[] = [
          'elasticsearch_index_batch_process',
          [$info],
        ];
      }

      // Set up the batch job.
      $batch = array(
        'operations' => $operations,
        'title' => t('Elasticsearch indexing'),
        'init_message' => t('Starting...'),
        'progress_message' => t('Completed @current of @total.'),
        'error_message' => t('An error occurred'),
        'finished' => 'elasticsearch_batch_finish',
        'file' => drupal_get_path('module', 'elasticsearch') . '/drush/elasticsearch.batch.inc',
      );

      // Start the batch job.
      batch_set($batch);
      drush_backend_batch_process();
    }
    else {
      drush_print('No items to process');
    }
  }
}

function drush_elasticsearch_elas_mapping_create($type = null) {
  $elasticsearch = new Elasticsearch();

  print $elasticsearch->create_index_mapping($type);
}

function drush_elasticsearch_elas_indexing_delete_all() {

  $elasticsearch = new Elasticsearch();
  if (drush_confirm(dt('Are you sure to continue the process ?'))) {
    $elasticsearch->delete_index_mapping_all();
  }
}

//function update_index_mapping($type = null) {
//
//  $elasticsearch = new ElasticsearchBase();
//  if (drush_confirm(dt('Are you sure to continue the process ?'))) {
//    if ($type == null) {
//      print "please add param node type";
//    }
//    else{
//      $elasticsearch->update_index_mapping($type);
//    }
//  }
//}
