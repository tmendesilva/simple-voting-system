<?php

declare(strict_types=1);

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\voting_system\QuestionInterface;
use Drupal\voting_system\Service\QuestionService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for a question entity type.
 */
final class VotingForm extends FormBase {

  /**
   * The question service.
   *
   * @var \Drupal\voting_system\Service\QuestionService */
  protected QuestionService $questionService;

  /**
   * The question entity.
   *
   * @var \Drupal\voting_system\QuestionInterface | null */
  protected QuestionInterface|null $questionEntity;

  public function __construct(
    QuestionService $question_service,
  ) {
    $this->questionService = $question_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('voting_system.question_service'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'voting_system_voting_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['#attributes']['novalidate'] = 'novalidate';

    $this->questionEntity = $this->questionService->loadRandomQuestion();
    if (!$this->questionEntity) {
      $form['message'] = [
        '#markup' => $this->t('No question available'),
      ];
      return $form;
    }

    $form['question_id'] = [
      '#type' => 'hidden',
      '#value' => $this->questionEntity->id(),
    ];

    $form['answer'] = [
      '#type' => 'radios',
      '#title' => $this->questionEntity->get('title')->value,
      '#options' => $this->getAnswerOptions(),
      '#required' => TRUE,
      '#default_value' => '',
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Vote'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $this->questionService->vote($form_state->getValue('answer'));
      $this->messenger()->addStatus($this->t('The vote has been recorded.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
      return;
    }
    $form_state->setRebuild();
  }

  /**
   * Gets answer options.
   */
  private function getAnswerOptions(): array {
    $answers = $this->questionService->getAnswerFieldValues($this->questionEntity);
    array_walk($answers, function (&$answer) {
      $answer['id'] = (string) $answer['id'];
    });
    return array_column($answers, 'title', 'id');
  }

}
