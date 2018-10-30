<?php

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

  /**
   * PHPUnit Tests for Content Hub Drush commands.
   *
   * This uses Drush's own test framework, based on PHPUnit.
   *
   * @group acquia_contenthub
   */
  class ContentHubDrushTest extends CommandUnishTestCase {

    protected $siteOptions = ['skip' => NULL, 'yes' => NULL];

    /**
     * Drupal setUp method.
     */
    public function setUp() {
      // Install the standard install profile.
      $sites = $this->setUpDrupal(1, TRUE, '8', 'standard');
      $site = key($sites);
      $root = $this->webroot();

      // Set site options for drush command.
      $this->siteOptions = [
        'root' => $root,
        'uri' => $site,
        'yes' => NULL,
      ];

      // Symlink acquia_contenthub module inside the site being tested, so that
      // it is available as a drush command.
      $target_contenthub = dirname(dirname(dirname(__DIR__)));
      \symlink($target_contenthub, $this->webroot() . '/modules/acquia_contenthub');
      $this->drush('cache-clear', ['drush'], $this->siteOptions);

      // Create Acquia Content Hub Module.
      $this->drush('pm-enable', ['acquia_contenthub'], $this->siteOptions);
    }

    /**
     * Runs tests for Drush commands.
     *
     * Commands tested: ach-connect, ach-purge, ach-restore, ach-logs,
     * ach-mapping.
     */
    public function testDrushCommands() {
      // Try to execute the ach-purge command without registration.
      $this->drush('ach-purge', [], $this->siteOptions, NULL, NULL, self::EXIT_ERROR, '-y');
      $output = $this->getErrorOutput();
      $this->assertContains('Error trying to connect to the Content Hub', $output);

      // Register the site by using ach-connect drush command.
      $this->drush(
        'ach-connect', [
          'admin',
          'admin',
          'http://127.0.0.1:5000',
          'mastersite',
        ],
        $this->siteOptions
      );
      $output = $this->getErrorOutput();
      $this->assertContains('The configuration options have been saved.', $output, 'Acquia Content Hub Admin Configuration saved correctly.');
      $this->assertContains('Successful Client registration', $output, 'Successful Client registration.');

      // Execute ach-purge command.
      $this->drush('ach-purge', [], $this->siteOptions, NULL, NULL, self::EXIT_SUCCESS, '-y');
      $output = $this->getOutput();
      $this->assertContains('PROCEED WITH CAUTION. THIS ACTION WILL PURGE ALL EXISTING ENTITIES IN YOUR CONTENT HUB SUBSCRIPTION.', $output);
      $this->assertContains('Your Subscription is being purged. All clients who have registered to received webhooks will be notified with a reindex webhook when the purge process has been completed.', $output);

      // Execute ach-restore command.
      $this->drush('ach-restore', [], $this->siteOptions, NULL, NULL, self::EXIT_SUCCESS, '-y');
      $output = $this->getOutput();
      $this->assertContains('PROCEED WITH CAUTION. THIS ACTION WILL ELIMINATE ALL EXISTING ENTITIES IN YOUR CONTENT HUB SUBSCRIPTION.', $output);
      $this->assertContains('Your Subscription is being restored. All clients who have registered to received webhooks will be notified with a reindex webhook when the restore process has been completed.', $output);

      // Execute ach-logs command.
      $this->drush('ach-logs', [], $this->siteOptions, NULL, NULL, self::EXIT_SUCCESS);
      $output = $this->getOutput();
      $this->assertContains('Timestamp', $output);
      $this->assertContains('Type', $output);
      $this->assertContains('Client', $output);
      $this->assertContains('Entity UUID', $output);
      $this->assertContains('ID', $output);
      $this->assertContains('Request ID', $output);
      $this->assertContains('Status', $output);

      // Execute ach-mapping command.
      $this->drush('ach-mapping', [], $this->siteOptions, NULL, NULL, self::EXIT_SUCCESS);
      $output = $this->getOutput();
      $mapping = json_decode($output, TRUE);

      // Because this test is working with the Plexus Mock, this expected output
      // has to match this:
      // https://github.com/acquia/content-hub-scripts/blob/master/plexus_mock/examples/_mapping.json.
      $expected = [
        'entity' => [
          'dynamic' => 'strict',
          'properties' => [
            'data' => [
              'dynamic' => 'strict',
              'properties' => [
                'assets' => [
                  'dynamic' => 'strict',
                  'properties' => [
                    'replace-token' => [
                      'type' => 'string',
                    ],
                    'url' => [
                      'type' => 'string',
                    ],
                  ],
                ],
                'attributes' => [
                  'dynamic' => 'true',
                  'properties' => [
                    'title' => [
                      'dynamic' => 'strict',
                      'properties' => [
                        'metadata' => [
                          'type' => 'string',
                        ],
                        'type' => [
                          'type' => 'string',
                        ],
                        'value' => [
                          'dynamic' => 'true',
                          'properties' => [
                            'und' => [
                              'type' => 'string',
                            ],
                          ],
                        ],
                      ],
                    ],
                  ],
                  'type' => 'object',
                ],
                'created' => [
                  'type' => 'date',
                ],
                'metadata' => [
                  'dynamic' => 'true',
                  'index' => 'no',
                  'type' => 'object',
                ],
                'modified' => [
                  'type' => 'date',
                ],
                'origin' => [
                  'type' => 'string',
                ],
                'type' => [
                  'type' => 'string',
                ],
                'uuid' => [
                  'type' => 'string',
                ],
              ],
            ],
            'id' => [
              'type' => 'string',
            ],
            'origin' => [
              'index' => 'not_analyzed',
              'type' => 'string',
            ],
            'revision' => [
              'type' => 'long',
            ],
            'subscription' => [
              'type' => 'string',
            ],
            'uuid' => [
              'index' => 'not_analyzed',
              'type' => 'string',
            ],
          ],
        ],
      ];
      $this->assertEquals($expected, $mapping);
    }

  }
}
