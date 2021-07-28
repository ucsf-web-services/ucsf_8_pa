<?php

namespace EclipseGc\CommonConsole\Command\Traits;

use Symfony\Component\Console\Input\InputInterface;

/**
 * Trait OptionsCheckerTrait.
 *
 * @package EclipseGc\CommonConsole\Command\Traits
 */
trait OptionsCheckerTrait {

  /**
   * Validates required input options' presence.
   *
   * @param \Symfony\Component\Console\Input\InputOption[] $options
   *   The subjects of validation.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   The input to compare against.
   *
   * @throws \Exception
   */
  protected function checkRequiredOptions(array $options, InputInterface $input): void {
    $missing_options = [];
    foreach ($options as $option) {
      $name = $option->getName();
      if ($option->isValueRequired() && !$input->getOption($name)) {
        $missing_options[] = $name;
      }
    }

    if (empty($missing_options)) {
      return;
    }

    throw new \Exception(sprintf('Missing required option(s): %s', join(', ', $missing_options)));
  }
  
}
