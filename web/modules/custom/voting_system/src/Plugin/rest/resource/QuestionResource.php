<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\voting_system\Entity\Question;
use Drupal\voting_system\Service\QuestionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a Question Resource.
 */
#[RestResource(
  id: "question_resource",
  label: new TranslatableMarkup("QuestionResource"),
  uri_paths: [
    "canonical" => "/vote-system/question/{id}",
    "create" => "/vote-system/question",
  ]
)]
class QuestionResource extends ResourceBase {

  use StringTranslationTrait;

  public const PAGE_SIZE = 5;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * The Request service.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  private Request $request;

  /**
   * The active status of the voting system.
   *
   * @var bool
   */
  private bool $isActive = FALSE;

  /**
   * The question service.
   *
   * @var \Drupal\voting_system\Service\QuestionService
   */
  private QuestionService $questionService;

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    ConfigFactoryInterface $config_factory,
    RequestStack $requestStack,
    QuestionService $question_service,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger, $config_factory);
    $this->serializerFormats = $serializer_formats;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
    $this->request = $requestStack->getCurrentRequest();

    $config = $this->configFactory->getEditable('voting_system.settings');
    $this->isActive = (bool) $config->get('voting_system_status') ?? TRUE;
    $this->questionService = $question_service;
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
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('voting_system.question_service'),
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
    if (!$id) {
      $qids = $this->questionService->getIds();
      $page = $this->request->query->get('page');
      if (!$page) {
        $page = 1;
      }
      $questionsEntities = Question::loadMultiple(array_slice($qids, ($page - 1) * self::PAGE_SIZE, self::PAGE_SIZE));
      $response = [
        'total' => count($qids),
        'page' => (int) $page,
        'totalPages' => ceil(count($qids) / self::PAGE_SIZE),
        'totalResults' => count($questionsEntities),
      ];
    }
    else {
      $questionsEntities[] = Question::load($id);
    }

    $questions = [];
    foreach ($questionsEntities as $questionEntity) {
      $question = $this->questionService->getFieldValues($questionEntity);
      $question['answers'] = $this->questionService->getAnswerFieldValues($questionEntity);
      $questions[] = $question;
    }
    $response['data'] = $questions;
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
    try {
      $data['answers'] = $this->questionService->createAnswers($data);
      $question = Question::create($data);
      $question->save();
      return new ModifiedResourceResponse([
        'message' => $this->t('Question created successfully'),
        'id' => $question->id(),
      ]);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Error on saving entity: @message', ['@message' => $e->getMessage()]),
      ], 500);
    }
  }

  /**
   * Updates a question.
   *
   * @param array $data
   *   The request data.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   */
  public function patch($data) {

    if (!isset($data['id'])) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Id key is required'),
      ], 400);
    }

    if (array_keys($data) !== ['id', 'status']) {
      return new ModifiedResourceResponse([
        'message' => $this->t("Only 'status' can be updated!"),
      ], 400);
    }

    $questionEntity = Question::load($data['id']);
    if (!$questionEntity) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Question @id not found', ['@id' => $data['id']]),
      ], 404);
    }

    try {
      $questionEntity->set('status', (bool) $data['status']);
      $questionEntity->save();
      return new ModifiedResourceResponse([
        'message' => $this->t('Question updated successfully'),
      ]);
    }
    catch (\Exception $e) {
      return new ModifiedResourceResponse([
        'message' => $this->t('Error on updating question: @message', ['@message' => $e->getMessage()]),
      ], 500);
    }
  }

}
