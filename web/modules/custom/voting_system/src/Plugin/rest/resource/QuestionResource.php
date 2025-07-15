<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\voting_system\Entity\Answer;
use Drupal\voting_system\Entity\Question;
use Psr\Log\LoggerInterface;

/**
 * Provides a Question Resource.
 */
#[RestResource(
  id: "question_resource",
  label: new TranslatableMarkup("QuestionResource"),
  uri_paths: [
    "canonical" => "/vote_system/question/{id}",
    "create" => "/vote_system/question",
  ]
)]
class QuestionResource extends ResourceBase {

  use StringTranslationTrait;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * The active status of the voting system.
   *
   * @var bool
   */
  private bool $isActive = FALSE;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $config_factory);
    $this->serializerFormats = $serializer_formats;
    $this->logger = $logger;
    $this->configFactory = $config_factory;

    $config = $this->configFactory->getEditable('voting_system.settings');
    $this->isActive = (bool) $config->get('voting_system_status') ?? TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $collection = parent::routes();
    // Add defaults for optional parameters.
    $defaults = [
      'id' => 0,
    ];
    foreach ($collection->all() as $route) {
      $route->addDefaults($defaults);
    }
    return $collection;
  }

  /**
   * Gets question details.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function get($id) {
    if ($id) {
      $questionsEntities[] = Question::load($id);
    }
    else {
      $questionsEntities = Question::loadMultiple();
    }
    $questions = [];
    foreach ($questionsEntities as $questionEntity) {
      $question = $this->getFieldValues($questionEntity);
      $question['answers'] = $this->getAnswerFieldValues($questionEntity);
      $questions[] = $question;
    }
    $response = [
      'data' => $questions,
    ];
    return new ModifiedResourceResponse($response);
  }

  /**
   * Creates new questions and answers.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Exception
   */
  public function post($data) {

    $data['answers'] = $this->createAnswers($data);
    $question = Question::create($data);
    try {
      $question->save();
      return new ModifiedResourceResponse([
        'message' => $this->t('Question created successfully'),
        'id' => $question->id(),
      ]);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Error saving question: @message', ['@message' => $e->getMessage()]),
      ], 400);
    }
  }

  /**
   * Adds a vote to an answer.
   *
   * @param array $data
   *   The request data.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function patch($data) {

    if (!$this->isActive) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Voting is temporarily disabled'),
      ], 503);
    }

    $questionId = $data['question'] ?? NULL;
    $questionEntity = Question::load($questionId);
    if (!$questionEntity) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Question @id not found', ['@id' => $questionId]),
      ], 404);
    }

    $answers = $this->getAnswerFieldValues($questionEntity);
    $answerIds = array_column($answers, 'id');
    $answerId = $data['answer'] ?? NULL;
    if (!in_array($answerId, $answerIds)) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Answer @id not found', ['@id' => $data['answer']]),
      ], 404);
    }

    $answerEntity = Answer::load($answerId);
    $answerEntity->set('votes', ((int) $answerEntity->get('votes')->value) + 1);

    try {
      $answerEntity->save();
      return new ModifiedResourceResponse([
        'message' => $this->t('Vote added successfully!'),
      ]);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Error on adding vote to question!'),
      ], 400);
    }
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
  protected function getFieldValues($entity) {
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
  protected function getAnswerFieldValues($questionEntity) {
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
  protected function createAnswers($data) {
    $answerIds = [];
    foreach ($data['answers'] ?? [] as $answerData) {
      $answerData['description'] = [
        'format' => 'plain_text',
        'value' => $answerData['description'],
      ];
      $answer = Answer::create($answerData);
      try {
        $answer->save();
        $answerIds[] = $answer->id();
      }
      catch (\Exception $e) {
        return new ModifiedResourceResponse([
          'message' => $this->t('Error saving answer: @message', ['@message' => $e->getMessage()]),
        ], 400);
      }
    }
    return $answerIds;
  }

}
