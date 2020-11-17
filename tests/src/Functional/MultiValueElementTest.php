<?php

declare(strict_types = 1);

namespace Drupal\Tests\multivalue_form_element\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the multi-value form element behaviour.
 *
 * @group multivalue_form_element
 */
class MultiValueElementTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'multivalue_form_element_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the multi-value form element.
   */
  public function testElement(): void {
    $this->drupalGet('/multivalue-form-element/element-test-form');

    $assert_session = $this->assertSession();
    // For unlimited cardinality elements, when no default value is provided,
    // only the first delta is rendered.
    $assert_session->fieldExists('foo[0][text]');
    $assert_session->elementNotExists('css', 'input[name^="foo[1]"]');

    // For limited cardinalities, all the deltas are rendered.
    $assert_session->fieldExists('bar[0][number]');
    $assert_session->fieldExists('bar[1][number]');
    $assert_session->fieldExists('bar[2][number]');
    $assert_session->elementNotExists('css', 'input[name^="bar[3]"]');

    // Add some default values.
    $this->setFormDefaultValues([
      'foo' => ['a', 'b'],
      'bar' => [1, 2, 3, 4],
    ]);
    $this->drupalGet('/multivalue-form-element/element-test-form');

    // For unlimited cardinality elements, elements get generated for each
    // delta of the default value.
    $this->assertEquals('a', $assert_session->fieldExists('foo[0][text]')->getValue());
    $this->assertEquals('b', $assert_session->fieldExists('foo[1][text]')->getValue());
    // One element is generated, with an empty value.
    $this->assertEmpty($assert_session->fieldExists('foo[2][text]')->getValue());
    // Next deltas are not rendered.
    $assert_session->elementNotExists('css', 'input[name^="foo[3]"]');

    // For limited cardinalities, extra values are discarded and only the
    // maximum cardinality is rendered.
    $this->assertEquals('1', $assert_session->fieldExists('bar[0][number]')->getValue());
    $this->assertEquals('2', $assert_session->fieldExists('bar[1][number]')->getValue());
    $this->assertEquals('3', $assert_session->fieldExists('bar[2][number]')->getValue());
    $assert_session->elementNotExists('css', 'input[name^="bar[3]"]');

    // Test that passing non-contiguous deltas is handled.
    $this->setFormDefaultValues([
      'foo' => [
        1 => 'c',
        5 => 'd',
      ],
    ]);
    $this->drupalGet('/multivalue-form-element/element-test-form');

    $this->assertEquals('c', $assert_session->fieldExists('foo[0][text]')->getValue());
    $this->assertEquals('d', $assert_session->fieldExists('foo[1][text]')->getValue());
    $assert_session->elementNotExists('css', 'input[name^="foo[5]"]');

    // Test behaviour for elements with multiple children.
    $this->setFormDefaultValues([
      'complex' => [
        [
          'text' => 'e',
          'number' => 5,
        ],
        [
          'text' => 'f',
          'number' => 6,
        ],
      ],
    ]);
    $this->drupalGet('/multivalue-form-element/element-test-form');

    $this->assertEquals('e', $assert_session->fieldExists('complex[0][text]')->getValue());
    $this->assertEquals('5', $assert_session->fieldExists('complex[0][number]')->getValue());
    $this->assertEquals('f', $assert_session->fieldExists('complex[1][text]')->getValue());
    $this->assertEquals('6', $assert_session->fieldExists('complex[1][number]')->getValue());
    // Verify that also in this case there is an extra rendered empty element.
    $this->assertEmpty($assert_session->fieldExists('complex[2][text]')->getValue());
    $this->assertEmpty($assert_session->fieldExists('complex[2][number]')->getValue());
    $assert_session->elementNotExists('css', 'input[name^="complex[3]"]');

    // Test that the add more button label can be overridden.
    $this->assertEquals('Add another item', $assert_session->buttonExists('foo_add_more')->getValue());
    $this->assertEquals('Add more complexity', $assert_session->buttonExists('complex_add_more')->getValue());

    // Test that the button name and AJAX wrapper ID are unique and take into
    // account the form structure.
    $wrapper = $assert_session->elementExists('css', 'div#nested-inner-foo-add-more-wrapper');
    $assert_session->buttonExists('nested_inner_foo_add_more', $wrapper);

    // Test that the add more button works correctly without JavaScript.
    $assert_session->buttonExists('foo_add_more')->press();
    $assert_session->fieldExists('foo[0][text]');
    $assert_session->fieldExists('foo[1][text]');
    $assert_session->elementNotExists('css', 'input[name^="foo[2]"]');
  }

  /**
   * Sets the default values for some form elements in the test form.
   *
   * @param array $default_values
   *   The default values to use in the form.
   */
  protected function setFormDefaultValues(array $default_values): void {
    \Drupal::state()->set('multivalue_form_element_test_default_values', $default_values);
  }

}
