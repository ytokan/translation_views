<?php

namespace Drupal\translation_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Provides filtering by translation status.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("translation_views_status")
 */
class TranslationStatusFilter extends BooleanOperator {

  public function getValueOptions() {
    $this->valueOptions = [1 => $this->t('Translated'), 0 => $this->t('Not translated')];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table_alias = $this->ensureMyTable();
    $status = $this->value;

    if ($status == 0) {
      $op = '=';
    }
    // Then status translated is the case.
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
