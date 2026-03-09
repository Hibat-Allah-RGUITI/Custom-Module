<?php

namespace Drupal\anytown\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Messenger\MessengerInterface;

class NodeSelectForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_select_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'article']);
    $options = [];
    foreach ($nodes as $node) {
      $options[$node->id()] = $node->getTitle();
    }

    $form['selected_node'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a node'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $selected_node = $form_state->getValue('selected_node');

    \Drupal::state()->set('anytown_selected_node', $selected_node);

    $this->messenger()->addMessage($this->t('Node @node saved.', ['@node' => $selected_node]));
  }
}