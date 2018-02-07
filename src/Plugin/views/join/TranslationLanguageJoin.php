<?php

namespace Drupal\translation_views\Plugin\views\join;

use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\join\JoinPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Special join to show all translatable langcodes per one row.
 *
 * @ViewsJoin("translation_views_language_join")
 */
class TranslationLanguageJoin extends JoinPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildJoin($select_query, $table, $view_query) {
    $query = $this->database->select($this->table, 'nfd');
    $query->fields('nfd', ['nid']);

    if (!empty($this->configuration['langcodes_as_count'])) {
      $query->addExpression("COUNT(nfd.langcode)", 'count_langs');
    }
    else {
      $query->addExpression("GROUP_CONCAT(nfd.langcode separator ',')", 'langs');
    }

    if (!empty($this->configuration['exclude_default_langcode'])) {
      $query->where('nfd.default_langcode != 1');
    }

    $query->groupBy('nfd.nid');
    $this->configuration['table formula'] = $query;

    parent::buildJoin($select_query, $table, $view_query);
  }

}
