<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\translation_views\TranslationCountTrait;
use Drupal\views\Plugin\views\field\NumericField;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\ViewsHandlerManager;

/**
 * Show translation count.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_translation_count")
 */
class TranslationCountField extends NumericField implements ContainerFactoryPluginInterface {
  use TranslationCountTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ViewsHandlerManager $join_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
    $this->joinHandler = $join_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.views.join')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    /** @var \Drupal\views\Plugin\views\query\Sql $query */
    $query = $this->query;
    $join_alias = $this->joinLanguages($query);
    $this->field_alias = $query->addField($join_alias, 'count_langs', $this->realField);
  }

}
