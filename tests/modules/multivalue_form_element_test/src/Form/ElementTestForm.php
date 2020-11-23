<?php

declare(strict_types = 1);

namespace Drupal\multivalue_form_element_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\multivalue_form_element\Element\MultiValue;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to test the multivalue form element.
 *
 * State can be used to pass the default values to use and to retrieve the
 * submitted values.
 */
class ElementTestForm extends FormBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs an ElementTestForm object.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('state'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multivalue_form_element_element_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_values = $this->state->get('multivalue_form_element_test_default_values', []);

    // An element with a single child and unlimited cardinality.
    $form['foo'] = [
      '#type' => 'multivalue',
      '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
      '#title' => $this->t('Foo'),
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
      ],
    ];

    // Add the default value for foo only if passed. In this way we can cover
    // the scenario when no #default_value key is passed.
    if (isset($default_values['foo'])) {
      $form['foo']['#default_value'] = $default_values['foo'];
    }

    // An element with a single child and limited cardinality.
    $form['bar'] = [
      '#type' => 'multivalue',
      '#cardinality' => 3,
      '#title' => $this->t('Bar'),
      'number' => [
        '#type' => 'number',
        '#title' => $this->t('Number'),
      ],
      '#default_value' => $default_values['bar'] ?? [],
    ];

    // An element with two children.
    $form['complex'] = [
      '#type' => 'multivalue',
      '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
      '#title' => $this->t('Complex'),
      '#add_more_label' => $this->t('Add more complexity'),
      'text' => [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
      ],
      'number' => [
        '#type' => 'number',
        '#title' => $this->t('Number'),
      ],
      '#default_value' => $default_values['complex'] ?? [],
    ];

    // A nested element, used to test the generation of the button name and
    // AJAX wrapper.
    $form['nested'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      'inner' => [
        '#type' => 'container',
        'foo' => [
          '#type' => 'multivalue',
          '#cardinality' => MultiValue::CARDINALITY_UNLIMITED,
          '#title' => $this->t('Inner foo'),
          'bar' => [
            '#type' => 'checkboxes',
            '#title' => $this->t('Values'),
            '#options' => [
              'a' => $this->t('Value A'),
              'b' => $this->t('Value B'),
            ],
          ],
        ],
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
    $this->state->set('multivalue_form_element_test_submitted_values', $form_state->getValues());
  }

}
