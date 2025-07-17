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
final class SystemConfigurationForm extends FormBase {

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
    return 'voting_system_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable voting system'),
      '#default_value' => $this->questionService->isActive(),
    ];

    $form['show_results'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow user to view poll results after answering questions'),
      '#default_value' => $this->questionService->showResultsAfterVote(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $this->questionService->setStatus((bool) $form_state->getValue('status'));
      $this->questionService->setShowResultsAfterVote((bool) $form_state->getValue('show_results'));
      $this->messenger()->addStatus($this->t('Configuration has been updated.'));
    }
    catch (\Exception $e) {
      $this->messenger()->addError($e->getMessage());
    }
  }

}
