<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Provides filtering by translation status.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("translation_views_status")
 */
class TranslationStatusFilter extends FilterPluginBase {

  const VALUE_UNTRANSLATED = 0;
  const VALUE_TRANSLATED = 1;
  const VALUE_BOTH = 2;

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    unset($form['expose']['multiple']);
    unset($form['expose']['required']);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['value']['default'] = self::VALUE_BOTH;

    return $options;
  }

  /**
   * {@inheritdoc}
   *
   * Remove "all" option because we don't need it.
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);

    if (!empty($this->options['expose']['identifier'])) {
      $value_id = $this->options['expose']['identifier'];
    }
    else {
      $value_id = 'translation_views_status';
    }

    unset($form[$value_id]['#options']['All']);
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    parent::valueForm($form, $form_state);

    $form['value'] = [
      '#type' => 'select',
      '#title' => $this->t('Status'),
      '#options' => [
        self::VALUE_BOTH => t('Both'),
        self::VALUE_UNTRANSLATED => t('Not translated'),
        self::VALUE_TRANSLATED => t('Translated'),
      ],
      '#multiple' => FALSE,
      '#default_value' => isset($this->value) ? $this->value : self::VALUE_BOTH,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table_alias = $this->ensureMyTable();

    $status = is_array($this->value)
      ? (int) reset($this->value)
      : $this->value;

    // We don't want to build and run query,
    // when user specified both statuses.
    if ($status == self::VALUE_BOTH) {
      return FALSE;
    }

    if ($status == self::VALUE_UNTRANSLATED) {
      $op = '=';
    }
    // Then self::VALUE_TRANSLATED is the case.
    else {
      // Mysql FIND_IN_SET func will return position of element,
      // when no element was found then it returns 0,
      // so we use "> 0" as condition to filter out untranslated rows.
      $op = '>';
      $status = 0;
    }

    /* @var \Drupal\views\Plugin\views\query\Sql */
    $this->query->addWhereExpression(
      $this->options['group'],
      "FIND_IN_SET(:langcode, $table_alias.langs) $op :status", [
        ':langcode' => '***TRANSLATION_VIEWS_TARGET_LANG***',
        ':status' => $status,
      ]
    );
  }

}
