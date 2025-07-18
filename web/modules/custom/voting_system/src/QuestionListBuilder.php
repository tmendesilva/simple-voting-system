<?php

declare(strict_types=1);

namespace Drupal\voting_system;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\voting_system\Service\QuestionService;

/**
 * Provides a list controller for the question entity type.
 */
final class QuestionListBuilder extends EntityListBuilder {

  /**
   * The question service.
   *
   * @var \Drupal\voting_system\Service\QuestionService
   */
  private QuestionService $questionService;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    QuestionService $question_service,
  ) {
    $this->entityTypeId = $entity_type->id();
    $this->storage = $storage;
    $this->entityType = $entity_type;
    $this->questionService = $question_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('voting_system.question_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entityQuery = $this->getStorage('voting_system_question')->getQuery();
    $header = $this->buildHeader();
    $entityQuery
      ->accessCheck(TRUE)
      ->pager(10)
      ->sort('id', 'DESC')
      ->tableSort($header);
    $ids = $entityQuery->execute();
    return $this->storage->loadMultiple($ids);
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['answers'] = $this->t('Answers');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {

    /** @var \Drupal\voting_system\QuestionInterface $entity */
    $question = $this->questionService->getFieldValues($entity);
    $answers = $this->questionService->getAnswerFieldValues($entity);

    $row['id'] = $question['id'];
    $row['title'] = $question['title'];
    $row['answers'] = $this->renderAnswers($answers);
    $row['status'] = $entity->get('status')->value ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

  /**
   *
   */
  private function renderAnswers(array $answers) {
    $renderarray = [
      '#theme' => 'voting_system_answer',
      '#answers' => $answers,
    ];
    return \Drupal::service('renderer')->renderInIsolation($renderarray);
  }

}
