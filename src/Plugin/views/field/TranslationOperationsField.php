<?php

namespace Drupal\translation_views\Plugin\views\field;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\translation_views\EntityInfo as HelperEntityInfo;
use Drupal\translation_views\TranslationViewsTargetLanguage as TargetLanguage;
use Drupal\views\Plugin\views\field\EntityOperations;
use Drupal\views\ResultRow;

/**
 * Renders translation operations links.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("translation_views_operations")
 */
class TranslationOperationsField extends EntityOperations {
  use TargetLanguage;

  /**
   * {@inheritdoc}
   *
   * Build operation links.
   */
  public function render(ResultRow $values) {
    /* @var ContentEntityInterface $entity */
    $entity       = $this->getEntity($values);
    $langcode_key = $this->buildSourceEntityLangcodeKey($entity);
    $operations   = $this->getOperations($entity, $values->{$langcode_key});

    if ($this->options['destination']) {
      foreach ($operations as &$operation) {
        if (!isset($operation['query'])) {
          $operation['query'] = [];
        }
        $operation['query'] += $this->getDestinationArray();
      }
    }
    $build = [
      '#type' => 'operations',
      '#links' => $operations,
    ];
    $build['#cache']['contexts'][] = 'url.query_args:target_language';

    return $build;
  }

  /**
   * Build value key.
   *
   * Value key based on base table,
   * and system name of langcode key (it might be differ then just 'langcode'),
   * usually table alias is [entity_type]_field_data_[langcode_key].
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Used to extract entity type info from entity.
   *
   * @return string
   *   The value key.
   */
  protected function buildSourceEntityLangcodeKey(ContentEntityInterface $entity) {
    return implode('_', [
      $this->view->storage->get('base_table'),
      $entity->getEntityType()->getKey('langcode'),
    ]);
  }

  /**
   * Operation links manager.
   *
   * Decide which links we should generate:
   * based on user permissions,
   * and entity state (has translation, is default, etc.).
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The source entity to get context for decision.
   * @param string $row_lang
   *   The target langcode of the row.
   *
   * @return array
   *   Operation links' render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function getOperations(ContentEntityInterface $entity, $row_lang) {
    $links   = [];
    $current = $this->getTargetLanguage()
      ? $this->getTargetLanguage()
      : $row_lang;

    /* @var \Drupal\content_translation\ContentTranslationHandlerInterface $handler */
    $handler = $this->getEntityManager()
      ->getHandler($entity->getEntityTypeId(), 'translation');

    // Construct special object to store common properties,
    // it will be used by all builder functions,
    // just as "trait" but for methods.
    $entity_info = new HelperEntityInfo(
      $entity,
      $handler,
      $this->languageManager->getLanguage($current),
      $this->languageManager->getLanguage($row_lang)
    );

    $is_source = $entity->getUntranslated()->language()->getId() === $entity_info->rowLanguage->getId();

    // Build edit & delete link.
    if (array_key_exists($current, $entity_info->translations)) {
      // If the user is allowed to edit the entity we point the edit link to
      // the entity form, otherwise if we are not dealing with the original
      // language we point the link to the translation form.
      if ($entity->access('update', NULL, TRUE)->isAllowed()
        && $entity_info->entityType->hasLinkTemplate('edit-form')
      ) {
        $links += $this->buildEditLink($entity_info, 'entity');
      }
      elseif (!$is_source && $entity_info->getTranslationAccess('update')) {
        $links += $this->buildEditLink($entity_info, 'translation');
      }

      // Build delete link.
      if (!$is_source) {
        if ($entity->access('delete')
          && $entity_info->entityType->hasLinkTemplate('delete-form')
        ) {
          $links += $this->buildDeleteLink($entity_info, 'entity');
        }
        elseif ($entity_info->getTranslationAccess('update')) {
          $links += $this->buildDeleteLink($entity_info, 'translation');
        }
      }
    }
    // Build add link.
    elseif (!empty($current)
      && $entity_info->translatable
      && $entity_info->getTranslationAccess('create')
    ) {
      // No such translation.
      $links += $this->buildAddLink($entity_info);
    }
    return $links;

  }

  /**
   * Add link builder.
   */
  protected function buildAddLink(HelperEntityInfo $entity_info) {
    $links = [];

    $add_url = new Url(
      "entity.{$entity_info->entityTypeId}.content_translation_add",
      [
        'source' => $entity_info->rowLanguage->getId(),
        'target' => $entity_info->currentLanguage->getId(),
        $entity_info->entityTypeId => $entity_info->entity->id(),
      ],
      [
        'language' => $entity_info->currentLanguage,
      ]
    );

    $links['add'] = [
      'title' => $this->t('Add'),
      'url' => $add_url,
    ];
    return $links;
  }

  /**
   * Delete link builder.
   */
  protected function buildDeleteLink(HelperEntityInfo $entity_info, $type = FALSE) {
    $links = [];

    if (!$type) {
      return $links;
    }

    if ($type == 'entity') {
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => $entity_info->entity->urlInfo('delete-form'),
        'language' => $entity_info->rowLanguage,
      ];
    }
    elseif ($type == 'translation') {
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => new Url(
          "entity.{$entity_info->entityTypeId}.content_translation_delete",
          [
            'language' => $entity_info->rowLanguage->getId(),
            $entity_info->entityTypeId => $entity_info->entity->id(),
          ],
          [
            'language' => $entity_info->rowLanguage,
          ]
        ),
      ];
    }
    return $links;
  }

  /**
   * Edit link builder.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function buildEditLink(HelperEntityInfo $entity_info, $type = FALSE) {
    $links = [];

    if ($type == 'entity') {
      $links['edit']['url'] = $entity_info->entity->toUrl('edit-form');
      $links['edit']['language'] = $entity_info->rowLanguage;
    }
    elseif ($type == 'translation') {
      $links['edit']['url'] = new Url(
        "entity.{$entity_info->entityTypeId}.content_translation_edit",
        [
          'language' => $entity_info->rowLanguage->getId(),
          $entity_info->entityTypeId => $entity_info->entity->id(),
        ],
        [
          'language' => $entity_info->rowLanguage,
        ]
      );
      ;
    }

    if (isset($links['edit'])) {
      $links['edit']['title'] = $this->t('Edit');
    }

    return $links;
  }

}
