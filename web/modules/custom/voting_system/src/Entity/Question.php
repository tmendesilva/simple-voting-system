<?php

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the question entity.
 *
 * @ingroup question
 *
 * @ContentEntityType(
 *   id = "question",
 *   label = @Translation("Question"),
 *   base_table = "question",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "answers" = "answers",
 *   },
 *   handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData"
 *   },
 *   field_ui_base_route="entity.question.settings"
 * )
 */
class Question extends ContentEntityBase implements ContentEntityInterface {

  /**
   *
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as unique if primary index.
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Question entity.'))
      ->setRequired(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Question entity.'))
      ->setRequired(TRUE);

    // Standard field, unique outside of the scope of the current project.
    $fields['answers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answers'))
      ->setDescription(t('The title of the Question entity.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'target_type' => 'answer',
        'default_value' => [],
      ])
      ->setRequired(TRUE);

    return $fields;
  }

}
