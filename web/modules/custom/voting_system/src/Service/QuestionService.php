<?php

namespace Drupal\voting_system\Service;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 *
 */
class QuestionService {

  /**
   * The question entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $questionEntityStorage;

  /**
   * The answer entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $answerEntityStorage;

  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->questionEntityStorage = $entity_type_manager->getStorage('voting_system_question');
    $this->answerEntityStorage = $entity_type_manager->getStorage('voting_system_answer');
  }

  /**
   * Gets field values.
   *
   * @param \Drupal\voting_system\Entity\Question $entity
   *   The question entity.
   *
   * @return array
   *   the field values
   */
  public function getFieldValues($entity) {
    $fieldValues = [];
    foreach ($entity->getFields() as $attr => $field) {
      $fieldValues[$attr] = $field->value;
    }
    return $fieldValues;
  }

  /**
   * Gets answer field values.
   *
   * @param \Drupal\voting_system\Entity\Question $questionEntity
   *   The answer entities.
   *
   * @return array
   *   The field values
   */
  public function getAnswerFieldValues($questionEntity) {
    $answerEntities = $questionEntity->get('answers')->referencedEntities();
    $answerValues = [];
    foreach ($answerEntities as $answerEntity) {
      $answerValues[] = $this->getFieldValues($answerEntity);
    }

    // Calculate percentage.
    $account = \Drupal::currentUser();
    if (in_array('rest_admin', $account->getRoles())) {
      $totalVotes = array_sum(array_column($answerValues, 'votes'));
      foreach ($answerValues as $i => $answerValue) {
        $answerValues[$i]['percentage'] = $totalVotes ? $answerValue['votes'] / $totalVotes : 0;
      }
    }

    return $answerValues;
  }

  /**
   * Creates answers.
   *
   * @param array $data
   *   The POST data.
   *
   * @return array
   *   The answer ids.
   */
  public function createAnswers($data) {
    $answerIds = [];
    foreach ($data['answers'] ?? [] as $answerData) {
      $answerData['description'] = [
        'format' => 'plain_text',
        'value' => $answerData['description'],
      ];
      $answer = $this->answerEntityStorage->create($answerData);
      $answer->save();
      $answerIds[] = $answer->id();
    }
    return $answerIds;
  }

}
