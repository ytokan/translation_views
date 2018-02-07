<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\Boolean;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a field that adds translation status.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_status")
 */
class TranslationStatus extends Boolean implements ContainerFactoryPluginInterface {
  use TargetLanguage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['type'] = ['default' => 'status'];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    $this->definition['output formats']['status'] = [
      t('Translated'), $this->t('Not translated'),
    ];

    parent::init($view, $display, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $langcode = $this->getTargetLanguage();

    /* @var $entity \Drupal\Core\TypedData\TranslationStatusInterface */
    $entity = $this->getEntity($values);

    $translation_status = $entity->getTranslationStatus($langcode);

    $values->{$this->field_alias} = $translation_status;
    return parent::render($values);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

}
