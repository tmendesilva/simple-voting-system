<?php

namespace Drupal\voting_system\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class VotingSystemController extends ControllerBase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * MyController constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   *
   */
  public function content() {
    $config = $this->configFactory->getEditable('voting_system.settings');
    $config->set('voting_system_status', !(bool) $config->get('voting_system_status'))->save();
    return new JsonResponse([
      'message' => $this->t('Voting system is @value.', [
        '@value.' => $config->get('voting_system_status') ? 'enabled' : 'disabled',
      ]),
    ]);
  }

}
