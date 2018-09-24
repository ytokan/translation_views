<?php

/**
 * @file
 * Provide views extra filters/fields for translator needs.
 *
 * @ingroup views_module_handlers
 */

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException as PluginNotFound;
use Drupal\translation_views\TranslationViewsFieldDefinitions as FieldDefinitions;

/**
 * Implements hook_views_data_alter().
 *
 * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
 */
function translation_views_views_data_alter(array &$data) {
  $content_translation_manager = \Drupal::service('content_translation.manager');

  foreach (\Drupal::entityTypeManager()->getDefinitions() as $entity_type_id => $entity_type) {
    if ($entity_type->hasHandlerClass('views_data')
      && $entity_type->isTranslatable()
      && $content_translation_manager->isEnabled($entity_type_id)
    ) {

      try {
        /** @var \Drupal\views\EntityViewsDataInterface $views_data */
        $views_data = \Drupal::entityTypeManager()->getHandler(
          $entity_type_id,
          'views_data'
        );
      }
      catch (PluginNotFound $e) {
        throw new PluginNotFound($entity_type_id, sprintf(
          'The "%s" entity type did not specify views_data handler class.',
          $entity_type->getLabel()
        ));
      }

      $field_data_table = $views_data->getViewsTableForEntityType($entity_type);

      if (!empty($field_data_table)) {
        $key = $entity_type_id . '_translation';
        $target_key = $key . '_target';
        $group = t('@entity_label translation', [
          '@entity_label' => $entity_type->getLabel(),
        ]);

        $translations_table =& $data[$key];
        $translations_table_target =& $data[$target_key];

        $pk = $entity_type->getKey('id');

        $translations_table['table'] = [
          'group'       => $group,
          'entity type' => $entity_type_id,
          'join'        => [
            $field_data_table => [
              'join_id'    => 'translation_views_language_join',
              'entity_pk'  => $pk,
              'type'       => 'LEFT',
              'left_field' => $pk,
              'field'      => $pk,
              'table'      => $field_data_table,
            ],
          ],
        ];

        $translations_table += FieldDefinitions::buildStatusField();
        $translations_table += FieldDefinitions::buildTargetLanguageField();
        $translations_table += FieldDefinitions::buildOpLinksField();
        $translations_table += FieldDefinitions::buildCountField();

        $translations_table_target['table'] = [
          'group' => $group,
          'join'  => [
            $field_data_table => [
              'type'       => 'LEFT',
              'left_field' => $pk,
              'field'      => $pk,
              'table'      => $field_data_table,
              'extra'      => [
                [
                  'field' => 'langcode',
                  'value' => '***TRANSLATION_VIEWS_TARGET_LANG***',
                ],
              ],
            ],
          ],
        ];

        $translations_table_target += FieldDefinitions::buildOutdatedField(
          $data[$field_data_table]['content_translation_outdated']
        );
        $translations_table_target += FieldDefinitions::buildSourceField(
          $data[$field_data_table]['content_translation_source']
        );
        $translations_table_target += FieldDefinitions::buildDefaultLangcodeField(
          $data[$field_data_table]['default_langcode']
        );
        if (isset($data[$field_data_table]['changed'])) {
          $translations_table_target += FieldDefinitions::buildChangedField(
            $data[$field_data_table]['changed']
          );
        }
      }
    }
  }

}
