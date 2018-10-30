<?php

namespace Drupal\acquia_contenthub_subscriber;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a Acquia Content Hub Filter entity.
 */
interface ContentHubFilterInterface extends ConfigEntityInterface {

  /**
   * Returns the Publish setting in human-readable format.
   *
   * @return string
   *   Returns the Human readable version of the publish setting.
   */
  public function getPublishSetting();

  /**
   * Returns the Drupal Publish status to use on nodes that matches this filter.
   *
   * @return bool|int
   *   0 if Unpublished status, 1 for Publish status, FALSE otherwise.
   */
  public function getPublishStatus();

  /**
   * Returns the Author.
   *
   * @return mixed
   *   Returns the author account name.
   */
  public function getAuthor();

  /**
   * Returns the filter conditions as an array.
   *
   * @return array
   *   Returns the filter conditions.
   */
  public function getConditions();

  /**
   * Change Date format from "m-d-Y" to "Y-m-d".
   */
  public function changeDateFormatMonthDayYear2YearMonthDay();

  /**
   * Change Date format from "Y-m-d" to "m-d-Y".
   */
  public function changeDateFormatYearMonthDay2MonthDayYear();

}
