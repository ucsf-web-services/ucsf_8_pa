<?php

namespace EclipseGc\CommonConsole\Event;

use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\Event;

class CreateApplicationEvent extends Event {

  /**
   * @var \Symfony\Component\Console\Application
   */
  protected $application;

  /**
   * CreateInputEvent constructor.
   *
   * @param \Symfony\Component\Console\Application $application
   *   The application being created.
   */
  public function __construct(Application $application) {
    $this->application = $application;
  }

  /**
   * @return \Symfony\Component\Console\Application
   */
  public function getApplication(): Application {
    return $this->application;
  }

}
