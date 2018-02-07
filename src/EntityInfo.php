<?php

namespace Drupal\translation_views;

use Drupal\content_translation\ContentTranslationHandlerInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Class EntityInfo.
 *
 * Special object to store common entity related properties,
 * it will be used by all operations link builder functions,
 * just as "trait" but for method's values.
 *
 * @package Drupal\translation_views
 */
class EntityInfo {

  /**
   * Source entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  public $entity;

  /**
   * The entity type id.
   *
   * @var string
   */
  public $entityTypeId;

  /**
   * Entity type instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  public $entityType;

  public $translations;

  public $translatable;

  public $rowLanguage;

  public $access;

  public $currentLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContentEntityInterface $entity, ContentTranslationHandlerInterface $access, LanguageInterface $language, LanguageInterface $row_lang) {
    $this->entity = $entity;
    $this->access = $access;

    $this->rowLanguage = $row_lang;
    $this->currentLanguage = $language;

    $this->entityTypeId = $entity->getEntityTypeId();
    $this->entityType   = $entity->getEntityType();
    $this->translations = $entity->getTranslationLanguages();
    $this->translatable = $entity->isTranslatable();
  }

  /**
   * An wrapper to get translation access by particular entity per operation.
   *
   * @see \Drupal\content_translation\ContentTranslationHandlerInterface::getTranslationAccess,
   * for available operations.
   */
  public function getTranslationAccess($op) {
    $access = $this->access->getTranslationAccess($this->entity, $op);
    return $access->isAllowed();
  }

}
