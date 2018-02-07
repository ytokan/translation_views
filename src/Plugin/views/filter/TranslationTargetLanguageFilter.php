<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\Plugin\views\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides filtering by translation target language.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("translation_views_target_language")
 */
class TranslationTargetLanguageFilter extends FilterPluginBase implements ContainerFactoryPluginInterface {
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
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    unset($form['expose']['multiple']);
    unset($form['expose']['required']);

    $form['expose']['identifier'] = [
      '#type' => 'hidden',
      '#value' => $this->options['expose']['identifier'],
      '#title' => $this->t('Filter identifier'),
      '#size' => 40,
      '#description' => $this->t('This will appear in the URL after the ? to identify this filter. Cannot be blank. Only letters, digits and the dot ("."), hyphen ("-"), underscore ("_"), and tilde ("~") characters are allowed.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['expose']['identifier'] = static::$targetExposedKey;
    $options['value']['default'] = '';
    $options['remove']['default'] = FALSE;

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $exposed = $form_state->get('exposed');

    if (!empty($this->options['exposed'])) {
      $identifier = $this->options['expose']['identifier'];
      $user_input = $form_state->getUserInput();

      // We need set exposed input when there is no selected value by user yet.
      if ($exposed && !isset($user_input[$identifier])) {
        $value = PluginBase::VIEWS_QUERY_LANGUAGE_SITE_DEFAULT;
        $this->setExposedValue($identifier, $value, $form_state);
      }
    }

    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Target language'),
      '#options' => $this->buildLanguageOptions(),
      '#multiple' => FALSE,
      '#weight' => 0,
      '#default_value' => $this->value,
    ];

    if (!$exposed) {
      $form['remove'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Remove rows where source language is equal to target language.'),
        '#default_value' => $this->options['remove'],
        '#weight' => -50,
      ];
    }
  }

  /**
   * Provide options for langcode dropdown.
   *
   * Options are based on configurable languages or site default one.
   */
  protected function buildLanguageOptions() {
    $site_default = $this->languageManager->getDefaultLanguage();
    $options = $this->listLanguages(LanguageInterface::STATE_CONFIGURABLE);

    if (isset($options[$site_default->getId()])) {
      unset($options[$site_default->getId()]);
    }

    return [PluginBase::VIEWS_QUERY_LANGUAGE_SITE_DEFAULT => $site_default->getName()] + $options;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    if ($this->options['remove']) {
      $this->query->addWhere(
        $this->options['group'],
        $this->view->storage->get('base_table') . '.langcode',
        '***TRANSLATION_VIEWS_TARGET_LANG***',
        '<>'
      );
    }
  }

  /**
   * Special setter for exposed value in views.
   */
  protected function setExposedValue($identifier, $value, FormStateInterface $form_state) {
    $user_input = $form_state->getUserInput();
    $user_input[$identifier] = $value;

    $form_state->setUserInput($user_input);
    $this->view->setExposedInput($user_input);
  }

}
