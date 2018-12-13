<?php

namespace Drupal\questionnaire;

use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\Account;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\RoleInterface;
// use Drupal\user\Entity\User;

/**
 * Class QuestionnaireManager.
 */
class QuestionnaireManager {

  public static function calculateUserAnswer($user_answer) {
    $score = 0;
    foreach ($user_answer as $key => $answer_id) {
      $answer = Paragraph::load($answer_id);
      if ($answer->field_correct_answer->value == 1) {
        $question = $answer->getParentEntity();
        $score += $question->field_question_score->value;
      }
    }
    return $score;
  }

  /**
   * Get the result id by questionnaire
   */
  public static function getUserResultId(NodeInterface $node) {
    $query = \Drupal::entityQuery('node');
    $query->condition('status', 1);
    $query->condition('type', 'questionnaire_result');
    $query->condition('field_result_questionnaire', $node->id());
    $query->condition('field_result_taker', \Drupal::currentUser()->id());
    $qid = $query->execute();
    return reset($qid);
  }

  public static function getUserQuestionAnswer($qid) {
    $answer = \Drupal::database()->select('node__field_result_answer_list', 'r');
    $answer->join('paragraph__field_aid', 'a', 'a.entity_id = r.field_result_answer_list_target_id');
    $answer->join('paragraph__field_qid', 'q', 'q.entity_id = r.field_result_answer_list_target_id');
    $answer->join('paragraph__field_answer', 'pa', 'pa.entity_id = a.field_aid_value');
    $answer->condition('q.field_qid_value', $qid);
    $answer->fields('pa', ['field_answer_value']);
    $answer = $answer->execute()->fetchAll(\PDO::FETCH_COLUMN, 0);
    return $answer[0];
  }


  // public function getAccountAccesses2(NodeInterface $node) {

  // }

    /**
   * {@inheritdoc}
   */
  public static function getAccountAccess(AccountInterface $account, NodeInterface $node) {
    // $current_user = \Drupal::currentUser();
    if ($account->hasPermission('access any questionnaire')) {
      // Site admin Role
      // Check if current user is allowed to view any questionnaire
      return TRUE;
    } else if ($account->hasPermission('view supervised questionnaire')) {
      // Supervisor Role
      // Check if current user is the referenced supervisor of the current viewed questionnaire
      if ($node->get('field_supervisor')->getString() == $account->id() ) {
        return TRUE;
      }
    } else if ($account->hasPermission('access own questionnaire')) {
      // Questionnaire Manager Role
      // The owner (author) has read/write permissions only to the Questionnaire nodes he/she created
      if ($node->getOwnerId() == $account->id()) {
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
   * Finds if current user has a custom role assigned or not.
   */
  public static function isUserHasCustomRole(AccountInterface $account) {
    $custom_roles = [
      'administrator',
      'site_admin',
      'supervisor',
      'manager',
    ];

    if (array_intersect($account->getRoles(), $custom_roles)) {
      return true;
    } else {
      return false;
    }
  }
}
