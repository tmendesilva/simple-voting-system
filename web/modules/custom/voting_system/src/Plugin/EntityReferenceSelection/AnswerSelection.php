<?php

namespace Drupal\voting_system\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Attribute\EntityReferenceSelection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\voting_system\AnswerInterface;

/**
 * Selection implementation of Entity Reference Selection plugin.
 */
#[EntityReferenceSelection(
  id: "default:answer",
  label: new TranslatableMarkup("Answer selection"),
  group: "default",
  weight: 1,
  entity_types: ["voting_system_answer"],
)]
class AnswerSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function createNewEntity($entity_type_id, $bundle, $label, $uid): EntityInterface|AnswerInterface {
    /** @var \Drupal\user\UserInterface $user */
    $answer = $this->entityTypeManager->getStorage('voting_system_answer')->create([
      'title' => $label,
      'description' => [
        'format' => 'plain_text',
        'value' => $label,
      ],
    ]);
    return $answer;
  }

}
