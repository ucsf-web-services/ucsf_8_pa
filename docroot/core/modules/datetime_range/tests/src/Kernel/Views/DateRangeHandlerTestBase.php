<?php

namespace Drupal\Tests\datetime_range\Kernel\Views;

use Drupal\Tests\datetime\Kernel\Views\DateTimeHandlerTestBase;

/**
 * Base class for testing datetime handlers.
 */
abstract class DateRangeHandlerTestBase extends DateTimeHandlerTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['datetime_test', 'node', 'datetime_range', 'field'];

  /**
   * Type of the field.
   *
   * @var string
   */
  protected static $field_type = 'daterange';

}
