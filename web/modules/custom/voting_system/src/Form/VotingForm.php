<?php

declare(strict_types=1);

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\voting_system\Service\QuestionService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form.
 */
final class VotingForm extends FormBase {

  /**
   * The question service.
   *
   * @var \Drupal\voting_system\Service\QuestionService */
  protected QuestionService $questionService;

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

    if (!$this->questionService->isActive()) {
      $form['message'] = [
        '#markup' => $this->t('Voting system is temporarily disabled.'),
      ];
      return $form;
    }

    $userInput = $form_state->getUserInput();
    $questionEntity = $this->questionService->loadQuestion((int) $userInput['question_id'] ?? NULL);

    if (!$questionEntity) {
      $form['message'] = [
        '#markup' => $this->t('No question available.'),
      ];
      return $form;
    }

    if ($form_state->get('showResults')) {

      $form['question'] = [
        '#markup' => $questionEntity->get('title')->value,
      ];

      $form['pool'] = [
        '#theme' => 'voting_system_answer',
        '#answers' => $this->questionService->getAnswerFieldValues($questionEntity),
      ];

      $form['actions'] = [
        '#type' => 'actions',
        'submit' => [
          '#type' => 'submit',
          '#value' => $this->t('Answer another question'),
        ],
      ];

      $form_state->set('loadAnotherQuestion', TRUE);

      return $form;
    }

    $form['question_id'] = [
      '#type' => 'hidden',
      '#value' => $questionEntity->id(),
    ];

    $form['question'] = [
      '#markup' => $questionEntity->get('title')->value,
    ];

    $options = $this->getAnswerOptions($questionEntity);
    $form['answer'] = [
      '#type' => 'radios',
      '#title' => '',
      '#options' => $options['options'],
      '#required' => TRUE,
    ] + $options['description'];

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
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    if ($form_state->get('loadAnotherQuestion')) {
      $form_state->cleanValues();
      $form_state->setRedirect('<front>');
      return;
    }

    try {
      $this->questionService->vote($form_state->getValue('answer'));
      $this->messenger()->addStatus($this->t('The vote has been recorded.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
    }

    if ($this->questionService->showResultsAfterVote()) {
      $form_state->set('showResults', TRUE);
      $form_state->setRebuild();
    }
  }

  /**
   * Gets answer options.
   */
  private function getAnswerOptions($questionEntity): array {
    $answers = $this->questionService->getAnswerFieldValues($questionEntity);
    $descriptions = [];
    foreach ($answers as $answer) {
      $descriptions[$answer['id']] = [
        '#description' => $answer['description'],
      ];
    }
    return [
      'options' => array_column($answers, 'title', 'id'),
      'description' => $descriptions,
    ];
  }

}
