<?php

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the answer entity.
 *
 * @ingroup answer
 *
 * @ContentEntityType(
 *   id = "answer",
 *   label = @Translation("Answer"),
 *   base_table = "answer",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "description" = "description",
 *     "votes" = "votes",
 *     "image" = "image",
 *   },
 *    handlers = {
 *     "views_data" = "Drupal\views\EntityViewsData"
 *   },
 *   field_ui_base_route="entity.answer.settings"
 * )
 */
class Answer extends ContentEntityBase implements ContentEntityInterface {

  /**
   * {@inheritDoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setRequired(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setRequired(TRUE);

    $fields['votes'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Votes'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    // $fields['image'] = BaseFieldDefinition::create('image')
    //   ->setLabel(t('Image'))
    //   ->setCardinality(1)
    //   ->setSettings([
    //     'target_type' => 'file',
    //     'handler' => 'default:file',
    //     'file_extensions' => 'png jpg jpeg',
    //     'default_image' => [
    //       'uuid' => NULL,
    //       'alt' => NULL,
    //       'title' => NULL,
    //     ],
    //   ]);
    return $fields;
  }

}
