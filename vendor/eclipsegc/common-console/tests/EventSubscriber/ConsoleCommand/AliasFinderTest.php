<?php

namespace EclipseGc\CommonConsole\Tests\EventSubscriber\ConsoleCommand;

use EclipseGc\CommonConsole\Tests\CommonConsoleTestBase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * Class AliasFinderTest.
 *
 * @group eclipsegc_common_console
 *
 * @coversDefaultClass \EclipseGc\CommonConsole\EventSubscriber\ConsoleCommand\AliasFinder
 *
 * @package EclipseGc\CommonConsole\Tests\EventSubscriber\ConsoleCommand
 */
class AliasFinderTest extends CommonConsoleTestBase {

  /**
   * @covers ::onConsoleCommand
   *
   * @dataProvider appParameterDataProvider
   */
  public function testOnConsoleCommand(array $parameters, string $expected, string $message) {
    /** @var \Symfony\Component\Console\Application $application */
    $application = $this->getContainer()->get('common_console_application');
    $application->setDispatcher($this->getContainer()->get('event_dispatcher'));
    $application->setAutoExit(FALSE);
    $app_tester = new ApplicationTester($application);

    $app_tester->run($parameters);
    $output = $app_tester->getDisplay();
    $this->assertStringContainsStringIgnoringCase($expected, $output, $message);
  }

  /**
   * Provides parameters for the application under test.
   */
  public function appParameterDataProvider() {
    return [
      [
        ['command' => 'list', '--help' => true],
        'list [options] [--] [<namespace>]',
        'Prints out the usage of list command',
      ],
      [
        ['--help' => true],
        'list [options] [--] [<namespace>]',
        'Prints out the usage of list command',
      ],
      [
        ['command' => 'list', '@foo'],
        'Alias by name foo not found. Please check your available aliases and try again.',
        'There is no such alias.',
      ],
      [
        ['command' => 'list', '--help' => true, '@foo'],
        'Alias by name foo not found. Please check your available aliases and try again.',
        'There is no such alias, if a flag was defined.',
      ],
      [
        ['command' => 'list', '@foo', '--help' => true],
        'Alias by name foo not found. Please check your available aliases and try again.',
        'There is no such alias, no matter where the alias was put.',
      ],
    ];
  }

}
