<?php

namespace EclipseGc\CommonConsole\Tests\Command;

use Consolidation\Config\Config;
use EclipseGc\CommonConsole\Command\PlatformCreate;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;
use EclipseGc\CommonConsole\Event\GetPlatformTypesEvent;
use EclipseGc\CommonConsole\Platform\PlatformFactory;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\QuestionFactory;
use EclipseGc\CommonConsole\Tests\CommonConsoleTestBase;
use EclipseGc\CommonConsole\Tests\Platform\FooPlatform;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlatformCreateTest extends CommonConsoleTestBase {

  /**
   * Test the PlatformCreate command.
   *
   * @throws \Exception
   */
  public function testPlatformCreate() {
    $dispatcher = $this->prophesize(EventDispatcherInterface::class);

    $dispatcher->dispatch(CommonConsoleEvents::GET_PLATFORM_TYPES, Argument::type(GetPlatformTypesEvent::class))->will(function($arguments) {
      /** @var GetPlatformTypesEvent $event */
      $event = $arguments[1];
      $event->addPlatformType('foo_platform');
    });

    $dispatcher->dispatch(CommonConsoleEvents::GET_PLATFORM_TYPE, Argument::type(GetPlatformTypeEvent::class))->will(function($arguments) {
      /** @var GetPlatformTypeEvent $event */
      $event = $arguments[1];
      $event->addClass(FooPlatform::class);
    });

    $storage = $this->prophesize(PlatformStorage::class);
    $storage->save(Argument::type(PlatformInterface::class))->will(function ($arguments) {
      return $arguments[0];
    })
    ->shouldBeCalledOnce();

    $factory = $this->prophesize(PlatformFactory::class);
    $factory->getMockPlatformFromConfig(Argument::type(Config::class), Argument::type(PlatformStorage::class))->will(function($arguments) {
      /** @var Config $config */
      $config = $arguments[0];
      /** @var PlatformStorage $storage */
      $storage = $arguments[1];
      return new FooPlatform($config->export(), $storage);
    });

    $questionFactory = new QuestionFactory($this->getContainer());

    $command = new PlatformCreate($dispatcher->reveal(), $storage->reveal(), $factory->reveal(), $questionFactory);
    $command->setApplication($this->getContainer()->get('common_console_application'));
    $commandTester = new CommandTester($command);
    $commandTester->setInputs([
      'foo_platform',
      'Foo Test',
      'footest',
    ]);
    $rc = $commandTester->execute(['command' => $command->getName()]);

    $this->assertEquals($this->getExpectedOutput(), $this->normalizeOutput($commandTester->getDisplay()));
    $this->assertEquals(0, $rc);
  }

  /**
   * Normalizes the output removing characters that won't be displayed.
   *
   * Remove unprintable characters and give autocomplete characters
   * representation so that we can compare output.
   *
   * @param string $string
   *
   * @return string|string[]|null
   */
  private function normalizeOutput(string $string) {
    return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $string);
  }

  /**
   * The expected command output.
   *
   * The autocomplete choice question prints odd characters as part of its
   * output, we need to represent those in our expected output.
   *
   * @return string
   */
  private function getExpectedOutput() {
    $expected = "This command will step you through the process of creating a new platform on which to perform common console commands.
Platform Type: 
  [0] foo_platform
 > f[K7oo_platform8o[K7o_platform8o[K7_platform8_[K7platform8p[K7latform8l[K7atform8a[K7tform8t[K7form8f[K7orm8o[K7rm8r[K7m8m[K78
Name: Alias: +----------------+--------------+
| Property       | Value        |
+----------------+--------------+
| platform.type  | foo_platform |
| platform.name  | Foo Test     |
| platform.alias | footest      |
+----------------+--------------+
Are these config correct? Successfully saved.
";
    return $this->normalizeOutput($expected);
  }

}
