<?php

declare(strict_types=1);

namespace Drupal\voting_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the answer entity type.
 */
final class AnswerListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['description'] = $this->t('Description');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\voting_system\AnswerInterface $entity */
    $row['id'] = $entity->id();
    $row['title'] = $entity->get('title')->value;
    $row['description'] = $entity->get('description')->value;
    $row['status'] = $entity->get('status')->value ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}
