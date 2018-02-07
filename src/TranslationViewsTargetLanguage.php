<?php

namespace Drupal\translation_views;

use Drupal\views\Plugin\views\PluginBase;

/**
 * Trait TranslationViewsTargetLanguage.
 *
 * Used to give ability to get selected target langcode,
 * used by different different fields, filters.
 *
 * @package Drupal\translation_views
 */
trait TranslationViewsTargetLanguage {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  public static $targetExposedKey = 'translation_target_language';

  /**
   * Get target language from exposed input.
   *
   * @return string
   *   Current selected target langcode
   */
  protected function getTargetLanguage() {
    $inputs   = $this->view->getExposedInput();
    $langcode = $inputs[self::$targetExposedKey];

    return $langcode == PluginBase::VIEWS_QUERY_LANGUAGE_SITE_DEFAULT
      ? $this->languageManager->getDefaultLanguage()->getId()
      : $langcode;
  }

}
