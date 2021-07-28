<?php

/**
 * @file
 * Contains mock objects of ACE environment responses.
 */

return [
  '111111-11111111-c36a-401a-9724-fd8072a607d7' => (object) [
    'id' => '111111-11111111-c36a-401a-9724-fd8072a607d7',
    'label' => 'Dev',
    'name' => 'dev',
    'application' =>
    (object) [
      'name' => 'example',
      'uuid' => '11111111-c36a-401a-9724-fd8072a607d7',
    ],
    'domains' =>
      [
        0 => 'example7nm4yg7jcb.devcloud.acquia-sites.com',
      ],
    'active_domain' => 'example7nm4yg7jcb.devcloud.acquia-sites.com',
    'default_domain' => 'example7nm4yg7jcb.devcloud.acquia-sites.com',
    'image_url' => NULL,
    'ssh_url' => 'example.dev@example7nm4yg7jcb.ssh.devcloud.acquia-sites.com',
    'ips' =>
      [
        0 => '127.0.0.1',
      ],
    'region' => 'us-east-1',
    'balancer' => 'balancers',
    'status' => 'normal',
    'type' => 'drupal',
    'size' => NULL,
    'weight' => 0,
    'vcs' =>
    (object) [
      'type' => 'git',
      'path' => 'master',
      'url' => 'example@svn-1111.devcloud.hosting.acquia.com:example.git',
    ],
    'flags' =>
    (object) [
      'cde' => FALSE,
      'hsd' => FALSE,
      'livedev' => FALSE,
      'migration' => FALSE,
      'multicert' => FALSE,
      'multi_region' => FALSE,
      'production' => FALSE,
      'production_mode' => FALSE,
      'remote_admin' => FALSE,
      'varnish' => TRUE,
      'varnish_over_ssl' => TRUE,
    ],
    'configuration' =>
    (object) [
      'operating_system' => 'xenial',
      'php' =>
      (object) [
        'apcu' => 32,
        'interned_strings_buffer' => NULL,
        'max_execution_time' => NULL,
        'max_input_vars' => NULL,
        'max_post_size' => NULL,
        'memory_limit' => 128,
        'opcache' => 96,
        'sendmail_path' => NULL,
        'version' => '7.2',
      ],
    ],
    'artifact' => NULL,
    '_links' =>
    (object) [
      'self' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7',
      ],
      'parent' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments',
      ],
      'alerts' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/alerts',
      ],
      'available-runtimes' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/available-runtimes',
      ],
      'connection-history' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/connection-history',
      ],
      'crons' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/crons',
      ],
      'databases' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases',
      ],
      'dns' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/dns',
      ],
      'domains' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/domains',
      ],
      'insight' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/insight',
      ],
      'logs' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/logs',
      ],
      'metrics' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/metrics',
      ],
      'modules' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/modules',
      ],
      'score-history' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/score-history',
      ],
      'settings' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/settings',
      ],
      'servers' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/servers',
      ],
      'ssl' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/ssl',
      ],
      'variables' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/variables',
      ],
    ],
    '_embedded' =>
    (object) [
      'application' =>
      (object) [
        'name' => 'example',
        'uuid' => '11111111-c36a-401a-9724-fd8072a607d7',
        '_links' =>
        (object) [
          'self' =>
          (object) [
            'href' => 'https://cloud.acquia.com/api/applications/11111111-c36a-401a-9724-fd8072a607d7',
          ],
          'parent' =>
          (object) [
            'href' => 'https://cloud.acquia.com/api/applications',
          ],
        ],
      ],
    ],
  ],
];
