<?php

declare(strict_types = 1);

namespace Drupal\Tests\multivalue_form_element\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the "Add more" functionality for multi-value elements.
 *
 * @group multivalue_form_element
 */
class AjaxAddMoreTest extends WebDriverTestBase {

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
   * Tests the AJAX "Add more" button.
   *
   * Test is shamelessly more-than-half copied from the core test.
   *
   * @see \Drupal\Tests\field\FunctionalJavascript\FormJSAddMoreTest::testFieldFormJsAddMore()
   */
  public function testAddMore(): void {
    $this->drupalGet('/multivalue-form-element/ajax-test-form');

    $assert_session = $this->assertSession();
    // Set a value on the first delta of the "foo" multi-value element.
    $field_foo_0 = $assert_session->fieldExists('foo[0][textfield]');
    $field_foo_0->setValue('1');

    // Add one more item.
    $button_add_more_foo = $assert_session->buttonExists('foo_add_more');
    $button_add_more_foo->press();
    $field_foo_1 = $assert_session->waitForField('foo[1][textfield]');
    $this->assertNotEmpty($field_foo_1);

    // Validate the value of the "foo" field has not changed.
    $this->assertEquals('1', $field_foo_0->getValue());

    // Validate the value of the second item is empty.
    $this->assertEmpty($field_foo_1->getValue());

    // Verify that only one extra delta was generated.
    $assert_session->fieldNotExists('foo[2][textfield]');

    // Verify that the "bar" multi-value element didn't change.
    $field_bar_0 = $assert_session->fieldExists('bar[0][textfield]');
    $this->assertEmpty($field_bar_0->getValue());
    $assert_session->fieldNotExists('bar[1][textfield]');

    // Add one more item for the "foo" element.
    $button_add_more_foo->press();
    $field_foo_2 = $assert_session->waitForField('foo[2][textfield]');
    $this->assertNotEmpty($field_foo_2);

    // Set values for the 2nd and 3rd fields to validate dragging.
    $field_foo_1->setValue('2');
    $field_foo_2->setValue('3');

    $field_weight_0 = $assert_session->fieldExists('foo[0][_weight]');
    $field_weight_1 = $assert_session->fieldExists('foo[1][_weight]');
    $field_weight_2 = $assert_session->fieldExists('foo[2][_weight]');

    // Assert starting situation matches expectations.
    $this->assertGreaterThan($field_weight_0->getValue(), $field_weight_1->getValue());
    $this->assertGreaterThan($field_weight_1->getValue(), $field_weight_2->getValue());

    // Drag the first row after the third row.
    $dragged = $field_foo_0->find('xpath', 'ancestor::tr[contains(@class, "draggable")]//a[@class="tabledrag-handle"]');
    $target = $field_foo_2->find('xpath', 'ancestor::tr[contains(@class, "draggable")]');
    $dragged->dragTo($target);

    // Assert the order of items is updated correctly after dragging.
    $this->assertGreaterThan($field_weight_2->getValue(), $field_weight_0->getValue());
    $this->assertGreaterThan($field_weight_1->getValue(), $field_weight_2->getValue());

    // Validate the order of items conforms to the last drag action after a
    // updating the form via the server.
    $button_add_more_foo->click();
    $field_foo_3 = $assert_session->waitForField('foo[3][textfield]');
    $this->assertNotEmpty($field_foo_3);
    $this->assertGreaterThan($field_weight_2->getValue(), $field_weight_0->getValue());
    $this->assertGreaterThan($field_weight_1->getValue(), $field_weight_2->getValue());

    // Validate no extra delta is displayed.
    $assert_session->fieldNotExists('foo[4][textfield]');

    // Add one item to the "bar" element.
    $button_add_more_bar = $assert_session->buttonExists('bar_add_more');
    $button_add_more_bar->press();
    $field_bar_1 = $assert_session->waitForField('bar[1][textfield]');
    $this->assertNotEmpty($field_bar_1);

    // Verify that the values and weights of the "bar" element were not impacted
    // by any changes on the "foo" element.
    $this->assertEmpty($field_bar_0->getValue());
    $this->assertEmpty($field_bar_1->getValue());
    $this->assertEquals(0, $assert_session->fieldExists('bar[0][_weight]')->getValue());
    $this->assertEquals(1, $assert_session->fieldExists('bar[1][_weight]')->getValue());
  }

}
