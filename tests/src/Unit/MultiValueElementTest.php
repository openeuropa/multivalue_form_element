<?php

declare(strict_types = 1);

namespace Drupal\Tests\multivalue_form_element\Unit;

use Drupal\Core\Form\FormState;
use Drupal\multivalue_form_element\Element\MultiValue;
use Drupal\Tests\UnitTestCase;

/**
 * Unit test for the MultiValue element class.
 *
 * @group multivalue_form_element
 * @coversDefaultClass \Drupal\multivalue_form_element\Element\MultiValue
 */
class MultiValueElementTest extends UnitTestCase {

  /**
   * Tests the valueCallback method.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   * @param mixed $input
   *   The incoming input to populate the form element. If this is FALSE,
   *   the element's default value should be returned.
   * @param mixed $expected
   *   The expected value.
   *
   * @covers ::valueCallback
   * @dataProvider valueCallbackDataProvider
   */
  public function testValueCallback(array $element, $input, $expected): void {
    $this->assertEquals($expected, MultiValue::valueCallback($element, $input, new FormState()));
  }

  /**
   * Data provider for the valueCallback test method.
   *
   * @return array
   *   An array of scenarios.
   */
  public function valueCallbackDataProvider(): array {
    $data = [];

    // When input is present, it's directly returned.
    $input = [0 => ['foo' => 'bar']];
    $data[] = [[], $input, $input];
    $data[] = [[], [], []];
    $data[] = [[], NULL, NULL];

    // Setup a base element with one children.
    $element = [
      '#type' => 'multivalue',
      '#property' => 'random',
      'foo' => [],
    ];

    // An empty array is set as default value if no #default_value is present.
    $data[] = [$element, FALSE, []];

    // Test the short notation available when only one child element is present
    // and the value is scalar.
    $element['#default_value'] = ['a', 'b'];
    $expected = [
      0 => [
        'foo' => 'a',
      ],
      1 => [
        'foo' => 'b',
      ],
    ];
    $data[] = [$element, FALSE, $expected];

    // Test that the full notation is accepted too when there's only one child
    // element.
    $element['#default_value'] = [
      0 => ['foo' => 'a'],
      1 => ['foo' => 'b'],
    ];
    // No changes are expected from the default value definition.
    $data[] = [$element, FALSE, $element['#default_value']];

    // The child key cannot be omitted if the value is an array, as no
    // processing will occur.
    $element['#default_value'] = [
      0 => ['a'],
    ];
    $data[] = [$element, FALSE, $element['#default_value']];

    // Add another child element to the main element.
    $element['bar'] = [];

    // When more than one child is present, default values are passed "as is".
    $element['#default_value'] = [
      0 => ['foo' => ['a'], 'bar' => 'b'],
      1 => ['foo' => ['c']],
      2 => ['bar' => 'd'],
      3 => 'e',
    ];
    $data[] = [$element, FALSE, $element['#default_value']];

    $element['#default_value'] = [
      '0' => ['foo' => 'a'],
      'invalid' => ['foo' => 'b'],
      1 => ['foo' => 'c'],
    ];
    $data[] = [
      $element,
      FALSE,
      [
        '0' => ['foo' => 'a'],
        1 => ['foo' => 'c'],
      ],
    ];

    return $data;
  }

}
