<?php

declare(strict_types=1);

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;
use Drupal\voting_system\AnswerInterface;
use Drupal\voting_system\AnswerListBuilder;
use Drupal\voting_system\Form\AnswerForm;
use Drupal\voting_system\Routing\AnswerHtmlRouteProvider;

/**
 * Defines the answer entity class.
 */
#[ContentEntityType(
  id: 'voting_system_answer',
  label: new TranslatableMarkup('Answer'),
  label_collection: new TranslatableMarkup('Answers'),
  label_singular: new TranslatableMarkup('answer'),
  label_plural: new TranslatableMarkup('answers'),
  entity_keys: [
    'id' => 'id',
    'title' => 'title',
    'description' => 'description',
    // 'image' => 'image',
    'votes' => 'votes',
    'langcode' => 'langcode',
    'published' => 'status',
    'label' => 'title',
  ],
  handlers: [
    'list_builder' => AnswerListBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'add' => AnswerForm::class,
      'edit' => AnswerForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
    ],
    'route_provider' => [
      'html' => AnswerHtmlRouteProvider::class,
    ],
  ],
  links: [
    'collection' => '/admin/content/answer',
    'add-form' => '/answer/add',
    'canonical' => '/answer/{voting_system_answer}',
    'edit-form' => '/answer/{voting_system_answer}',
    'delete-form' => '/answer/{voting_system_answer}/delete',
    'delete-multiple-form' => '/admin/content/answer/delete-multiple',
  ],
  admin_permission: 'administer voting_system_answer',
  base_table: 'voting_system_answer',
  data_table: 'voting_system_answer_field_data',
  translatable: TRUE,
  label_count: [
    'singular' => '@count answers',
    'plural' => '@count answers',
  ],
  field_ui_base_route: 'entity.voting_system_answer.settings',
)]
class Answer extends ContentEntityBase implements AnswerInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setTranslatable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setTranslatable(TRUE)
      ->setLabel(t('Image'))
      ->setSettings([
        'file_directory' => 'image',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg gif',
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'label' => 'hidden',
        'type' => 'image_image',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['votes'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Votes'))
      ->setDefaultValue(0)
      ->setRequired(TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 5,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
