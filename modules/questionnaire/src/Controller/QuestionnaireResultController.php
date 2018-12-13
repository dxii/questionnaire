<?php

namespace Drupal\questionnaire\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\questionnaire\QuestionnaireManager;
use Drupal\Core\Access\AccessResult;

/**
 * Defines Questionnaire class.
 */
class QuestionnaireResultController extends ControllerBase {

  /**
   * Display all results on a particlular questionnaire.
   *
   * @return array
   *   Return markup array.
   */
  public function quetionnaireResultPage(NodeInterface $node) {
    // get questionnaire result view
    $content = [
      '#type' => 'view',
      '#name' => 'questionnaire_results',
      '#display_id' => 'result_per_questionnaire',
      '#arguments' => [$node->id()],
    ];
    return $content;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, NodeInterface $node) {
    // Check user has correct access
    if (QuestionnaireManager::getAccountAccess($account, $node)) {
      // Add tab to questionnaire content type
      return AccessResult::allowedif($node->bundle() === 'questionnaire');
    }
    return AccessResult::forbidden();
  }
}
