<?php

declare(strict_types=1);

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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

    $questionEntity = $this->questionService->loadRandomQuestion();

    $form['question_id'] = [
      '#type' => 'hidden',
      '#value' => $questionEntity->id(),
    ];

    $form['question_text'] = [
      '#markup' => $questionEntity->get('title')->value,
    ];

    $answers = $this->questionService->getAnswerFieldValues($questionEntity);
    // $allowedValues = $this->questionService->getAnswerAllowedValues();
    $options = array_column($answers, 'title', 'id');
    var_dump($options);
    die();
    $form['answer'] = [
      '#type' => 'radios',
      '#title' => '',
      '#options' => $options,
      '#default_value' => NULL,
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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The configuration has been updated.'));
  }

}
