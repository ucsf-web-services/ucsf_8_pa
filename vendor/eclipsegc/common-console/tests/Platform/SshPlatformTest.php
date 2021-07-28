<?php

namespace EclipseGc\CommonConsole\Tests\Platform;

use Consolidation\Config\Config;
use EclipseGc\CommonConsole\Command\PlatformList;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\Platform\SshPlatform;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\ProcessRunner;
use EclipseGc\CommonConsole\Tests\CommonConsoleTestBase;
use Prophecy\Argument;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SshPlatformTest extends CommonConsoleTestBase {

  /**
   * Test the ssh platform command execution.
   */
  public function testSshExecution() {
    $config = new Config();
    $config->set('ssh.remote_vendor_dir', '/var/www/foo');
    $config->set('ssh.url', 'example.com');
    $config->set('ssh.user', 'foo');

    $self = $this;

    $process_callback = function (Process $process) use ($self) {
      $self->assertEquals('ssh foo@example.com \'./var/www/foo/bin/commoncli \'platform:list\'\'', $process->getCommandLine());
      return 'ssh foo@example.com \'./var/www/foo/bin/commoncli \'platform:list\'\'' === $process->getCommandLine();
    };
    $platform_callback = function (PlatformInterface $platform) use ($config, $self) {
      $self->assertEquals($config->export(), $platform->export());
      return $config->export() === $platform->export();
    };
    $runner = $this->prophesize(ProcessRunner::class);
    $runner->run(Argument::that($process_callback), Argument::that($platform_callback), Argument::type(OutputInterface::class))
      ->shouldBeCalledOnce()
      ->willReturn(0);

    $storage = $this->prophesize(PlatformStorage::class);

    $platform = new SshPlatform($config, $runner->reveal(), $storage->reveal());

    $command = new PlatformList($storage->reveal());

    $rc = $platform->execute($command, new ArrayInput([$command->getName()]), $this->getTestOutput());
    $this->assertEquals(0, $rc);
  }

}
