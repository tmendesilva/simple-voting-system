<?php

namespace Drupal\voting_system\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\voting_system\QuestionInterface;

/**
 * Question service class.
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

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  private FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file url generator.
   */
  public function __construct(
    EntityTypeManager $entity_type_manager,
    ConfigFactoryInterface $config_factory,
    FileUrlGeneratorInterface $file_url_generator,
  ) {
    $this->questionEntityStorage = $entity_type_manager->getStorage('voting_system_question');
    $this->answerEntityStorage = $entity_type_manager->getStorage('voting_system_answer');
    $this->configFactory = $config_factory;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * Gets system status.
   */
  public function isActive() :bool {
    $config = $this->configFactory->get('voting_system.settings');
    return (bool) $config->get('voting_system_status');
  }

  /**
   * Sets system status.
   */
  public function setStatus($status) {
    $config = $this->configFactory->getEditable('voting_system.settings');
    $config->set('voting_system_status', $status)->save();
  }

  /**
   * Gets config for show results after vote.
   */
  public function showResultsAfterVote() :bool {
    $config = $this->configFactory->get('voting_system.settings');
    return (bool) $config->get('show_results');
  }

  /**
   * Sets config for show results after vote.
   */
  public function setShowResultsAfterVote($status) {
    $config = $this->configFactory->getEditable('voting_system.settings');
    $config->set('show_results', $status)->save();
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
      if ($attr === 'image') {
        $fileUri = $field?->entity?->getFileUri();
        $fieldValues[$attr] = $fileUri ? $this->fileUrlGenerator->generateAbsoluteString($field?->entity?->getFileUri()) : NULL;
      }
      else {
        $fieldValues[$attr] = $field->value;
      }
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

    $totalVotes = array_sum(array_column($answerValues, 'votes'));
    foreach ($answerValues as $i => $answerValue) {
      $answerValues[$i]['percentage'] = $totalVotes ? $answerValue['votes'] / $totalVotes : 0;
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

  /**
   * Gets question ids.
   */
  public function getIds() {
    $qids = $this->questionEntityStorage->getQuery()
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();
    return $qids;
  }

  /**
   * Loads a random question.
   *
   * @return \Drupal\Core\Entity\QuestionInterface|null
   *   The random entity object, or NULL if no entity is found.
   */
  public function loadQuestion(int|null $questionId = NULL): QuestionInterface|null {

    if ($questionId) {
      return $this->questionEntityStorage->load($questionId);
    }

    // Random question.
    $query = Database::getConnection()->select('voting_system_question_field_data', 'q')
      ->fields('q', ['id'])
      ->condition('status', 1);
    $query->orderRandom()
      ->range(0, 1);
    $result = $query->execute()->fetchAssoc();
    if ($result) {
      return $this->questionEntityStorage->load($result['id']);
    }

    return NULL;
  }

  /**
   * Gets answer allowed values.
   */
  public function getAnswerAllowedValues() :array {
    $answerOptions = Database::getConnection()->select('voting_system_question_field_data', 'q')
      ->fields('q', ['id'])
      ->condition('status', 1)
      ->execute()
      ->fetchAllKeyed(0, 1);
    return array_keys($answerOptions);
  }

  /**
   * Computes votes.
   */
  public function vote($answerId) {
    $answerEntity = $this->answerEntityStorage->load($answerId);
    $answerEntity->set('votes', ((int) $answerEntity->get('votes')->value) + 1);
    $answerEntity->save();
  }

}
