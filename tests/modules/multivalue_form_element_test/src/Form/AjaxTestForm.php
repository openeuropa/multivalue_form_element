<?php

declare(strict_types = 1);

namespace Drupal\multivalue_form_element_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\multivalue_form_element\Element\MultiValue;

/**
 * Form to test the AJAX functionalities of multi-value elements.
 */
class AjaxTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multivalue_form_element_ajax_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['foo'] = [
      '#type' => 'multivalue',
      '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
      '#title' => $this->t('Multiple one'),
      'textfield' => [
        '#type' => 'textfield',
        '#title' => $this->t('Textfield one'),
      ],
    ];

    $form['bar'] = [
      '#type' => 'multivalue',
      '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
      '#title' => $this->t('Multiple two'),
      'textfield' => [
        '#type' => 'textfield',
        '#title' => $this->t('Texfield two'),
      ],
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No operation.
  }

}
