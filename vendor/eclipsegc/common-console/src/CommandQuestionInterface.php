<?php


namespace EclipseGc\CommonConsole;

/**
 * Interface CommandQuestionInterface
 *
 * @package EclipseGc\CommonConsole
 */
interface CommandQuestionInterface {

  /**
   * Return an array of \Symfony\Component\Console\Question\Question objects.
   *
   * The array of Question objects can be represented either directly as
   * Questions or as a callback to another public static method which evaluates
   * the values and returns a question.
   *
   * Example:
   *  public static function getPlatformQuestions() {
   *    return [
   *      'ssh_user' => new Question("SSH Username: "),
   *      'ssh_url' => new Question("SSH URL: "),
   *      'ssh_remote_dir' => new Question("SSH remote directory: "),
   *      'ssh_remote_vendor_dir' => [SshPlatform::class, 'getRemoteVendorDir'],
   *      'example_question_with_service_injection' => [
   *        'question' => [SshPlatform::class, 'getExampleQuestionWithServiceInjection'],
   *        'services' => ['some.service.id', 'some.other.service.id']
   *    ];
   *  }
   *
   *  public static function getRemoteVendorDir(array $values) {...
   *
   * @return array
   */
  public static function getQuestions();

}
