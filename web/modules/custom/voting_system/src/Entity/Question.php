<?php

declare(strict_types=1);

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Form\DeleteMultipleForm;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\views\EntityViewsData;
use Drupal\voting_system\Form\QuestionForm;
use Drupal\voting_system\QuestionInterface;
use Drupal\voting_system\QuestionListBuilder;

/**
 * Defines the question entity class.
 */
#[ContentEntityType(
  id: 'voting_system_question',
  label: new TranslatableMarkup('Question'),
  label_collection: new TranslatableMarkup('Questions'),
  label_singular: new TranslatableMarkup('question'),
  label_plural: new TranslatableMarkup('questions'),
  entity_keys: [
    'id' => 'id',
    'title' => 'title',
    'langcode' => 'langcode',
    'published' => 'status',
  ],
  handlers: [
    'list_builder' => QuestionListBuilder::class,
    'views_data' => EntityViewsData::class,
    'form' => [
      'add' => QuestionForm::class,
      'edit' => QuestionForm::class,
      'delete' => ContentEntityDeleteForm::class,
      'delete-multiple-confirm' => DeleteMultipleForm::class,
    ],
    'route_provider' => [
      'html' => AdminHtmlRouteProvider::class,
    ],
  ],
  links: [
    'collection' => '/admin/content/question',
    'add-form' => '/question/add',
    'canonical' => '/question/{voting_system_question}',
    'edit-form' => '/question/{voting_system_question}/edit',
    'delete-form' => '/question/{voting_system_question}/delete',
    'delete-multiple-form' => '/admin/content/question/delete-multiple',
  ],
  admin_permission: 'administer voting_system_question',
  base_table: 'voting_system_question',
  data_table: 'voting_system_question_field_data',
  translatable: TRUE,
  label_count: [
    'singular' => '@count questions',
    'plural' => '@count questions',
  ],
  field_ui_base_route: 'entity.voting_system_question.settings',
)]
class Question extends ContentEntityBase implements QuestionInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setTranslatable(TRUE)
      ->setLabel(t('Title'))
      ->setDescription(t('The question.'))
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

    $fields['answers'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Answers'))
      ->setDescription(t('The answers of the question.'))
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSettings([
        'target_type' => 'voting_system_answer',
        'default_value' => [],
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'match_limit' => 10,
          'size' => 60,
          'placeholder' => t('Enter here answer title...'),
        ],
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'voting_system_answer',
        'weight' => -10,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

}
