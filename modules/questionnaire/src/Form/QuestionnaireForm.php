<?php
/**
* @file
* Contains \Drupal\questionnaire\Form\QuestionnaireForm.
*/
namespace Drupal\questionnaire\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\questionnaire\QuestionnaireManager;

class QuestionnaireForm extends FormBase {
  private $nid = null;
  private $node = null;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'questionnaire_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node = null) {
    $this->node = $node;

    $user_has_result = QuestionnaireManager::getUserResultId($node);
    // Display this message if user has taken this questionnaire.
    if ($user_has_result) {
      $review_page = Link::fromTextAndUrl(t('Review your score here.'), Url::fromRoute('questionnaire.review', ['node' => $this->node->id()]))->toString();

      $markup = '<div class="questionnaire-markup">You\'ve already taken this quiz. ' . $review_page . '</div>';
      $form['markup'] = array(
        '#type' => 'markup',
        '#markup' => $markup
      );
    }

    $questions = $node->field_question->referencedEntities();
    foreach ($questions as $key => $question) {
      $answers = $question->field_question_answer_list->referencedEntities();
      $options = [];
      $item = 'A';
      foreach ($answers as $key => $answer) {
        $options[$answer->id()] = $item . '. ' . $answer->field_answer->value;
        $item++;
      }
      $form['questions'][$question->id()] = [
        '#type' =>'radios',
        '#title' => $question->field_question_title->value,
        '#options' => $options,
        '#required' => TRUE
      ];
    }

    // Display this button if user has not taken this questionnaire
    if (!$user_has_result) {
      $form['actions']['send'] = array('#type' => 'submit',
      '#value' => t('Submit'),
      '#name' => 'send',
      '#attributes' => ['class' => ['btn', 'btn-primary']],
      '#submit' => array('::submitForm'));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $node = null) {
    $account = \Drupal::currentUser();
    $user = User::load($account->id());
    $answers = $form_state->cleanValues()->getValues();

    $score = QuestionnaireManager::calculateUserAnswer($answers);

    $user_answers = [];
    foreach ($answers as $key => $answer) {
      $paragraph = Paragraph::create(['type' => 'user_answers',]);
      $paragraph->set('field_qid', $key);
      $paragraph->set('field_aid', $answer);
      $paragraph->isNew();
      $paragraph->save();
      $user_answers[] = $paragraph;
    }

    // Save user result
    $node = Node::create([
      'type' => 'questionnaire_result',
      'title' => $user->getUsername() . '\'s Result to QID: ' . $this->node->id(),
      'field_result_taker' => $user->id(),
      'field_result_questionnaire' => $this->node->id(),
      'field_result_score' => $score,
      'field_result_answer_list' => $user_answers
    ]);
    $node->save();

    // Redirect user to review page to check his result.
    $url = Url::fromRoute('questionnaire.review', ['node' => $this->node->id()]);
    $form_state->setRedirectUrl($url);
  }
}
