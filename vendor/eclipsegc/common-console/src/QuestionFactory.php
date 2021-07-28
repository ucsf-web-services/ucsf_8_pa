<?php

namespace EclipseGc\CommonConsole;

use Consolidation\Config\Config;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class QuestionFactory
 *
 * Questions can be dynamically built together on some commands, this factory
 * provides a generic catch-all solution for instantiating questions and
 * injecting appropriate dependencies into them.
 *
 * For more information on using this factory, check the
 * CommandQuestionInterface documentation.
 *
 * @see \EclipseGc\CommonConsole\CommandQuestionInterface
 *
 * @package EclipseGc\CommonConsole
 */
class QuestionFactory {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * QuestionFactory constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * Identifies and instantiates questions as necessary.
   *
   * @param mixed $question
   * @param \Consolidation\Config\Config $config
   *
   * @return \Symfony\Component\Console\Question\Question
   */
  public function getQuestion($question, Config $config): Question {
    if ($question instanceof Question) {
      return $question;
    }
    if (is_callable($question)) {
      return call_user_func_array($question, [$config]);
    }
    if (!empty($question['question']) && !empty($question['services']) && is_callable($question['question'])) {
      $services = $question['services'];
      $args = [
        $config
      ];
      foreach ($services as $service_id) {
        if (!$this->container->has($service_id)) {
          throw new LogicException(sprintf("Missing service id %s when trying to build a question.", $service_id));
        }
        $args[] = $this->container->get($service_id);
      }
      return call_user_func_array($question['question'], $args);
    }
    throw new LogicException("No question was created.");
  }

}
