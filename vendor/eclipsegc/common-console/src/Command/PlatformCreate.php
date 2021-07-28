<?php

namespace EclipseGc\CommonConsole\Command;

use Consolidation\Config\Config;
use EclipseGc\CommonConsole\CommonConsoleEvents;
use EclipseGc\CommonConsole\Event\GetPlatformTypeEvent;
use EclipseGc\CommonConsole\Event\GetPlatformTypesEvent;
use EclipseGc\CommonConsole\Event\PlatformConfigEvent;
use EclipseGc\CommonConsole\Platform\PlatformFactory;
use EclipseGc\CommonConsole\Platform\PlatformStorage;
use EclipseGc\CommonConsole\PlatformInterface;
use EclipseGc\CommonConsole\QuestionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class CreatePlatform
 *
 * @package EclipseGc\CommonConsole\Command
 */
class PlatformCreate extends Command {

  /**
   * {@inheritdoc}
   */
  protected static $defaultName = 'platform:create';

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The platform storage.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformStorage
   */
  protected $storage;

  /**
   * The platform factory.
   *
   * @var \EclipseGc\CommonConsole\Platform\PlatformFactory
   */
  protected $factory;

  /**
   * The question factory.
   *
   * @var \EclipseGc\CommonConsole\QuestionFactory
   */
  protected $questionFactory;

  /**
   * CreatePlatform constructor.
   */
  public function __construct(EventDispatcherInterface $dispatcher, PlatformStorage $storage, PlatformFactory $factory, QuestionFactory $questionFactory, string $name = NULL) {
    parent::__construct($name);
    $this->dispatcher = $dispatcher;
    $this->storage = $storage;
    $this->factory = $factory;
    $this->questionFactory = $questionFactory;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setDescription('Create a new platform on which to execute common console commands.');
    $this->setAliases(['pc']);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $output->writeln('<info>This command will step you through the process of creating a new platform on which to perform common console commands.</info>');
    $table = new Table($output);
    $table->setHeaders(['Property', 'Value']);
    $helper = $this->getHelper('question');
    /** @var \Symfony\Component\Console\Question\Question[] $questions */
    $event = new GetPlatformTypesEvent();
    $this->dispatcher->dispatch(CommonConsoleEvents::GET_PLATFORM_TYPES, $event);
    $question = new ChoiceQuestion("Platform Type: ", $event->getPlatformTypes());
    $platform_type = $helper->ask($input, $output, $question);
    $table->addRow([PlatformInterface::PLATFORM_TYPE_KEY, $platform_type]);

    $config = new Config();
    $config->set(PlatformInterface::PLATFORM_TYPE_KEY, $platform_type);

    $questions = [
      PlatformInterface::PLATFORM_NAME_KEY => new Question("Name: "),
      PlatformInterface::PLATFORM_ALIAS_KEY => new Question("Alias: "),
    ];
    $platform_event = new GetPlatformTypeEvent($platform_type);
    $this->dispatcher->dispatch(CommonConsoleEvents::GET_PLATFORM_TYPE, $platform_event);
    $platform_class = $platform_event->getClass();
    $questions += $platform_class::getQuestions();
    do {
      foreach ($questions as $variable => $question) {
        $question = $this->questionFactory->getQuestion($question, $config);
        $config->set($variable, $helper->ask($input, $output, $question));
        if (is_array($config->get($variable))) {
          foreach ($config->get($variable) as $value) {
            $table->addRow([$variable, $value]);
          }
        }
        else {
          $table->addRow([$variable, $config->get($variable)]);
        }
      }
      $table->render();

      $quest = new ConfirmationQuestion('Are these config correct? ');
      $answer = $helper->ask($input, $output, $quest);
    } while ($answer !== TRUE);
    try {
      $event = new PlatformConfigEvent($config, $input, $output);
      $this->dispatcher->dispatch(CommonConsoleEvents::PLATFORM_CONFIG, $event);
      if ($event->hasError()) {
        throw new \Exception(implode(', ', $event->getErrors()));
      }
      $platform = $this->factory->getMockPlatformFromConfig($event->getConfig(), $this->storage);
      $platform->save();
      $output->writeln("Successfully saved.");
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>The platform was not successfully saved.\nERROR: %s</error>", $exception->getMessage()));
    }
  }

}
