<?php

declare(strict_types = 1);

namespace Drupal\multivalue_form_element_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\multivalue_form_element\Element\MultiValue;

/**
 * Form to test multi-value elements marked as required.
 */
class RequiredElementsTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multivalue_form_element_required_elements_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // An element marked as required, with two children without any required
    // property.
    $form['required'] = [
      '#type' => 'multivalue',
      '#cardinality' => 2,
      '#title' => $this->t('Required'),
      '#required' => TRUE,
      'foo' => [
        '#type' => 'textfield',
        '#title' => $this->t('Foo'),
      ],
      'bar' => [
        '#type' => 'number',
        '#title' => $this->t('Bar'),
      ],
    ];

    // A required element with two children, but only one of them is marked
    // as required.
    $form['partial_required'] = [
      '#type' => 'multivalue',
      '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
      '#title' => $this->t('Partial required'),
      '#required' => TRUE,
      'baz' => [
        '#type' => 'textfield',
        '#title' => $this->t('Baz'),
        '#required' => TRUE,
      ],
      'qux' => [
        '#type' => 'number',
        '#title' => $this->t('Qux'),
      ],
    ];

    // This element is not set as required, but it has mistakenly some children
    // marked as required.
    $form['not_required'] = [
      '#type' => 'multivalue',
      '#cardinality' => 2,
      '#title' => $this->t('Not required'),
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
        '#required' => TRUE,
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
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
