<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\rest\Attribute\RestResource;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a Question Resource.
 */
#[RestResource(
  id: "question_resource",
  label: new TranslatableMarkup("QuestionResource"),
  uri_paths: [
    "canonical" => "/vote_system/question",
  ]
)]
class QuestionResource extends ResourceBase {

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $response = ['message' => 'Hello, this is question service'];
    return new ResourceResponse($response);
  }

}
