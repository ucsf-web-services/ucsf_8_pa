<?php

namespace EclipseGc\CommonConsole\Command;

use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class PlatformDelete.
 *
 * @package EclipseGc\CommonConsole\Command
 */
class PlatformDelete extends Command {
  
  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'platform:delete';

  /**
   * The platform storage.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $storage;

  /**
   * PlatformDelete constructor.
   *
   * @param \EclipseGc\CommonConsole\Platform\PlatformStorage $storage
   *   The platform storage service.
   * @param string|NULL $name
   *   The name of this command.
   */
  public function __construct(PlatformStorage $storage, string $name = NULL) {
    parent::__construct($name);
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Deletes the specified platform.')
      ->setHelp('Delete the platform configuration file. The argument should be an existing platform, omit the "@" sign.')
      ->setAliases(['pdel']);
  }

  /**
   * {@inheritdoc}
   */
  protected function initialize(InputInterface $input, OutputInterface $output) {
    $alias = $input->getArgument('alias');
    if (!$alias) {
      throw new \Exception('Command requires the "alias" argument!');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $alias = $input->getArgument('alias');
    $platform = $this->storage->load($alias);
    if (!$platform) {
      $output->writeln(sprintf('<error>Such platform "%s" does not exist!</error>', $alias));
      return 1;
    }
    $helper = $this->getHelper('question');
    $quest = new ConfirmationQuestion(sprintf('Are you certain you want to delete the %s platform? ', $platform->get(PlatformInterface::PLATFORM_NAME_KEY)));
    $answer = $helper->ask($input, $output, $quest);
    if (!$answer) {
      $output->writeln("Delete aborted.");
      return 2;
    }
    try {
      $this->storage->delete($platform);
      $output->writeln("Successfully deleted.");
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>The platform was not successfully deleted.\nERROR: %s</error>", $exception->getMessage()));
    }

    return 0;
  }

}
