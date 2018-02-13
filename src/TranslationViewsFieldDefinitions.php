<?php

namespace Drupal\translation_views;

/**
 * Class TranslationViewsFieldDefinitions.
 *
 * @package Drupal\translation_views
 */
final class TranslationViewsFieldDefinitions {

  /**
   * Ttranslation status field definition.
   */
  public static function buildStatusField() {
    return [
      'translation_status' => [
        'field_name' => 'status',
        'real field' => 'langcode',
        'title' => t('Translation status'),
        'field' => [
          'id' => 'translation_views_status',
          'help' => t('A boolean indicating whether the node is translated into target language.'),
          'additional fields' => [
            'content_translation_source' => 'content_translation_source',
          ],
        ],
        'filter' => [
          'id' => 'translation_views_status',
          'help' => t('A boolean indicating whether the node is translated into target language.'),
        ],
      ],
    ];
  }

  /**
   * Translations count field definition.
   */
  public static function buildCountField() {
    return [
      'translation_count' => [
        'field_name' => 'counter',
        'real field' => 'translation_langs',
        'title' => t('Translation counter'),
        'field' => [
          'id' => 'translation_views_translation_count',
          'help' => t('Amount of translations (without default language).'),
        ],
        'filter' => [
          'id' => 'translation_views_translation_count',
          'help' => t('Filter rows by amount of translations (without default language).'),
        ],
      ],
    ];
  }

  /**
   * Translation changed field definition.
   */
  public static function buildChangedField($definition) {
    $field = [];
    $field['translation_changed'] = $definition;
    $field['translation_changed']['real field'] = 'changed';
    $field['translation_changed']['field']['id'] = 'date';
    $field['translation_changed']['field']['help'] = t('The time that the node was last edited in target language.');
    $field['translation_changed']['title'] = t('Translation changed time');
    return $field;
  }

  /**
   * Translation outdated field definition.
   */
  public static function buildOutdatedField($definition) {
    $field = [];
    $field['translation_outdated'] = $definition;
    $field['translation_outdated']['real field'] = 'content_translation_outdated';
    $field['translation_outdated']['field']['id'] = 'boolean';
    $field['translation_outdated']['filter']['id'] = 'boolean';
    $field['translation_outdated']['field']['help'] = t('A boolean indicating whether the target language translation is outdated');
    $field['translation_outdated']['filter']['help'] = t('A boolean indicating whether the target language translation is outdated');
    $field['translation_outdated']['title'] = t('Translation outdated');
    return $field;
  }

  /**
   * Translation source field definition.
   */
  public static function buildSourceField($definition) {
    $field = [];
    $field['translation_source'] = $definition;
    $field['translation_source']['real field'] = 'content_translation_source';
    $field['translation_source']['field']['id'] = 'translation_views_source_equals_row';
    $field['translation_source']['filter']['id'] = 'translation_views_source_equals_row';
    $field['translation_source']['field']['help'] = t('A boolean indicating whether the translation source of target language is same as the row language');
    $field['translation_source']['filter']['help'] = t('A boolean indicating whether the translation source of target language is same as the row language');
    $field['translation_source']['title'] = t('Translation source equals row language');
    return $field;
  }

  /**
   * Translation default langcode field definition.
   */
  public static function buildDefaultLangcodeField($definition) {
    $field = [];
    $field['translation_default'] = $definition;
    $field['translation_default']['real field'] = 'default_langcode';
    $field['translation_default']['field']['id'] = 'boolean';
    $field['translation_default']['filter']['id'] = 'boolean';
    $field['translation_default']['filter']['accept null'] = TRUE;
    $field['translation_default']['field']['help'] = t('A boolean indicating whether the target language is default language');
    $field['translation_default']['filter']['help'] = t('A boolean indicating whether the target language is default language');
    $field['translation_default']['title'] = t('Target language equals default language');
    return $field;
  }

  /**
   * Translation operations field definition.
   */
  public static function buildOpLinksField() {
    $field['translation_operations'] = [
      'title' => t('Translation operations'),
      'help' => t('Provides links to perform translation operations.'),
      'field' => [
        'id' => 'translation_views_operations',
      ],
    ];
    return $field;
  }

  /**
   * Translation target language field definition.
   */
  public static function buildTargetLanguageField() {
    $field['translation_target_language']['field'] = [
      'title' => t('Target language'),
      'id' => 'translation_views_target_language',
      'help' => t('The target language.'),
    ];
    $field['translation_target_language']['filter'] = [
      'title' => t('Target language'),
      'id' => 'translation_views_target_language',
      'help' => t('Define the target language for other translation fields/filters. This filter should in most cases be used only as exposed filter.'),
    ];
    return $field;
  }

}