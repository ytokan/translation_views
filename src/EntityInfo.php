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
  /**
   * Entity translation languages.
   *
   * @var \Drupal\Core\Language\LanguageInterface[]
   */
  public $translations;
  /**
   * Entity is translatable flag.
   *
   * @var bool
   */
  public $translatable;
  /**
   * Access handler.
   *
   * @var \Drupal\content_translation\ContentTranslationHandlerInterface
   */
  public $access;
  /**
   * Source language object.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  public $sourceLanguage;
  /**
   * Target language object.
   *
   * @var \Drupal\Core\Language\LanguageInterface
   */
  public $targetLanguage;

  /**
   * {@inheritdoc}
   */
  public function __construct(ContentEntityInterface $entity, ContentTranslationHandlerInterface $access, LanguageInterface $target_lang, LanguageInterface $source_lang) {
    $this->entity = $entity;
    $this->access = $access;

    $this->sourceLanguage = $source_lang;
    $this->targetLanguage = $target_lang;

    $this->entityTypeId = $entity->getEntityTypeId();
    $this->entityType   = $entity->getEntityType();
    $this->translations = $entity->getTranslationLanguages();
    $this->translatable = $entity->isTranslatable();
  }

  /**
   * A wrapper for getting translation access per operation.
   *
   * @see \Drupal\content_translation\ContentTranslationHandlerInterface::getTranslationAccess,
   * for available operations.
   */
  public function getTranslationAccess($op) {
    $access = $this->access->getTranslationAccess($this->entity, $op);
    return $access->isAllowed();
  }

}
