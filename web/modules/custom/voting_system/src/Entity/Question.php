<?php

declare(strict_types=1);

namespace Drupal\voting_system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\voting_system\Form\QuestionForm;
use Drupal\voting_system\QuestionInterface;
use Drupal\voting_system\QuestionListBuilder;

/**
 * Defines the question entity type.
 */
#[ConfigEntityType(
  id: 'question',
  label: new TranslatableMarkup('Question'),
  label_collection: new TranslatableMarkup('Questions'),
  label_singular: new TranslatableMarkup('question'),
  label_plural: new TranslatableMarkup('questions'),
  config_prefix: 'question',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
  ],
  handlers: [
    'list_builder' => QuestionListBuilder::class,
    'form' => [
      'add' => QuestionForm::class,
      'edit' => QuestionForm::class,
      'delete' => EntityDeleteForm::class,
    ],
  ],
  links: [
    'collection' => '/admin/structure/question',
    'add-form' => '/admin/structure/question/add',
    'edit-form' => '/admin/structure/question/{question}',
    'delete-form' => '/admin/structure/question/{question}/delete',
  ],
  admin_permission: 'administer question',
  label_count: [
    'singular' => '@count question',
    'plural' => '@count questions',
  ],
  config_export: [
    'id',
    'label',
    'description',
  ],
)]
final class Question extends ConfigEntityBase implements QuestionInterface {

  /**
   * The example ID.
   */
  protected string $id;

  /**
   * The example label.
   */
  protected string $label;

  /**
   * The example description.
   */
  protected string $description;

}
