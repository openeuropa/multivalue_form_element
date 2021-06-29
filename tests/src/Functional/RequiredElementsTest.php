<?php

declare(strict_types = 1);

namespace Drupal\Tests\multivalue_form_element\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the required property behaviour on multi-value elements.
 *
 * @group multivalue_form_element
 */
class RequiredElementsTest extends BrowserTestBase {

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
   * Tests that the required property is correctly applied.
   */
  public function testRequiredProperty(): void {
    $this->drupalGet('/multivalue-form-element/required-elements-test-form');

    $assert_session = $this->assertSession();
    // The "required" element has no children marked as required, so all the
    // children of the first delta should be marked as required.
    $this->assertTrue($this->isFieldRequired('required[0][foo]'));
    $this->assertTrue($this->isFieldRequired('required[0][bar]'));
    // The other deltas are not marked as required.
    $this->assertFalse($this->isFieldRequired('required[1][foo]'));
    $this->assertFalse($this->isFieldRequired('required[1][bar]'));

    // The "partial_required" element has the "text" children marked as
    // required, but not the other.
    $this->assertTrue($this->isFieldRequired('partial_required[0][baz]'));
    $this->assertFalse($this->isFieldRequired('partial_required[0][qux]'));

    // Adding an item will trigger the error validation for this multi-value
    // element.
    $assert_session->buttonExists('partial_required_add_more')->press();
    $this->assertSession()->pageTextContains('Baz field is required.');
    // Test that the add more button limits correctly the validation.
    $this->assertSession()->pageTextNotContains('Foo field is required.');
    $this->assertSession()->pageTextNotContains('Bar field is required.');

    $assert_session->fieldExists('partial_required[0][baz]')->setValue($this->randomString());
    $assert_session->buttonExists('partial_required_add_more')->press();
    // The newly added delta has no required elements.
    $this->assertFalse($this->isFieldRequired('partial_required[1][baz]'));
    $this->assertFalse($this->isFieldRequired('partial_required[1][qux]'));

    // Test that multi-value elements not marked as required make children not
    // required too.
    $this->assertFalse($this->isFieldRequired('not_required[0][text]'));
  }

  /**
   * Returns if a field is marked required through the HTML attribute.
   *
   * @param string $field
   *   The field selector.
   *
   * @return bool
   *   If the field is required.
   */
  protected function isFieldRequired(string $field): bool {
    return $this->assertSession()->fieldExists($field)->hasAttribute('required');
  }

}
