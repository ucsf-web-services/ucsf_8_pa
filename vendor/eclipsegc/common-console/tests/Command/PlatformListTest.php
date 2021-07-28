<?php

namespace EclipseGc\CommonConsole\Tests\Command;

use EclipseGc\CommonConsole\Command\PlatformList;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\Tests\CommonConsoleTestBase;
use Symfony\Component\Console\Input\ArrayInput;

class PlatformListTest extends CommonConsoleTestBase {

  /**
   * @throws \Symfony\Component\Console\Exception\ExceptionInterface
   *
   * @covers \EclipseGc\CommonConsole\Command\PlatformList::execute
   *
   * @dataProvider listDataProvider
   */
  public function testList(array $platforms, array $input, array $expected_messages, array $expected_newlines, array $expected_options, int $expected_rc) {
    $storage = $this->prophesize(PlatformStorage::class);
    $storage->loadAll()->willReturn($platforms);
    $command = new PlatformList($storage->reveal());
    $input = new ArrayInput($input);
    $output = $this->getTestOutput();
    $rc = $command->run($input, $output);
    $actual = $output->getOutput();
    $this->assertEquals($expected_messages, $actual['messages']);
    $this->assertEquals($expected_newlines, $actual['newline']);
    $this->assertEquals($expected_options, $actual['options']);
    $this->assertEquals($expected_rc, $rc);
  }

  /**
   * A data provider for ::testList()
   *
   * @return array[]
   */
  public function listDataProvider() {
    $platform1 = $this->prophesize(PlatformInterface::class);
    $platform1->getAlias()->willReturn('foo');
    $platform1->get('platform.type')->willReturn('foo_type');
    $platform2 = $this->prophesize(PlatformInterface::class);
    $platform2->getAlias()->willReturn('bar');
    $platform2->get('platform.type')->willReturn('bar_type');
    return [
      // No platforms.
      [
        // Platforms
        [],
        // Input
        [],
        // Messages
        [
          0 => 'No platform available.',
        ],
        // Newlines
        [],
        // Options
        [0 => 0],
        // Return Code
        0,
      ],
      // Single platform
      [
        // Platforms
        [
          $platform1->reveal(),
        ],
        // Input
        [],
        // Messages
        [
          0 => '+-------+----------+',
          1 => '|<info> Alias </info>|<info> Type     </info>|',
          2 => '+-------+----------+',
          3 => '| foo   | foo_type |',
          4 => '+-------+----------+',
        ],
        // Newlines
        [],
        // Options
        [
          0 => 0,
          1 => 0,
          2 => 0,
          3 => 0,
          4 => 0,
        ],
        // Return Code
        0,
      ],
      // Multiple platforms
      [
        // Platforms
        [
          $platform1->reveal(),
          $platform2->reveal()
        ],
        // Input
        [],
        // Messages
        [
          0 => '+-------+----------+',
          1 => '|<info> Alias </info>|<info> Type     </info>|',
          2 => '+-------+----------+',
          3 => '| foo   | foo_type |',
          4 => '| bar   | bar_type |',
          5 => '+-------+----------+',
        ],
        // Newlines
        [],
        // Options
        [
          0 => 0,
          1 => 0,
          2 => 0,
          3 => 0,
          4 => 0,
          5 => 0,
        ],
        // Return Code
        0,
      ],
    ];
  }

}
