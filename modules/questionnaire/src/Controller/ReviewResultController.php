<?php
/**
* @file
* Contains \Drupal\questionnaire\Controller\ReviewResultController
*/
namespace Drupal\questionnaire\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\questionnaire\QuestionnaireManager;
use Drupal\Core\Session\AccountProxy;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

class ReviewResultController extends ControllerBase {

  /**
   * Returns a Result review page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function reviewResultPage(NodeInterface $node) {
    $rid = QuestionnaireManager::getUserResultId($node);
    $result = Node::load($rid);
    $content['node'] = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node, 'review');
    $content['score'] = $result->field_result_score->value . ' out of ' . $node->field_total_score->value;
    $content['title'] = $node->getTitle();

    $build = array(
      '#theme' => 'questionnaire_review_page',
      '#content' => $content,
    );
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, NodeInterface $node) {
    $rid = QuestionnaireManager::getUserResultId($node);
    // Check user has correct access
    if ($rid) {
      // Add tab to questionnaire content type
      return AccessResult::allowedif($node->bundle() === 'questionnaire');
    }
    return AccessResult::forbidden();
  }
}
