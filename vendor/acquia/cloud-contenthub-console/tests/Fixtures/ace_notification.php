<?php

/**
 * @file
 * Mock Response for Notifications Requests.
 */

return [
  'backup_create' => (object) [
    'uuid' => '1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
    'event' => 'Database backup event',
    'label' => 'Database created',
    'description' => 'Database backup',
    'created_at' => '2019-07-29T20:47:13+00:00',
    'completed_at' => '2019-07-29T20:47:13+00:00',
    'status' => 'completed',
    'progress' => 100,
    'context' =>
      [
        'author' =>
          [
            'uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
            'actual_uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
          ],
        'user' =>
          [
            'uuids' =>
              [
                0 => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
              ],
          ],
      ],
    '_links' => [
      'self' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications/1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
        ],
      'parent' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications',
        ],
    ],
  ],
  'backup_delete' => (object) [
    'uuid' => '1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
    'event' => 'Database backup event',
    'label' => 'Database backup deleted',
    'description' => 'Database backup',
    'created_at' => '2020-07-29T20:47:13+00:00',
    'completed_at' => '2020-07-29T20:47:13+00:00',
    'status' => 'completed',
    'progress' => 100,
    'context' =>
      [
        'author' =>
          [
            'uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
            'actual_uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
          ],
        'user' =>
          [
            'uuids' =>
              [
                0 => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
              ],
          ],
      ],
    '_links' => [
      'self' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications/1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
        ],
      'parent' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications',
        ],
    ],
  ],
  'backup_restore' => (object) [
    'uuid' => '1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
    'event' => 'Database backup event',
    'label' => 'Database backup restored',
    'description' => 'Database backup',
    'created_at' => '2020-07-29T20:47:13+00:00',
    'completed_at' => '2020-07-29T20:47:13+00:00',
    'status' => 'completed',
    'progress' => 100,
    'context' =>
      [
        'author' =>
          [
            'uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
            'actual_uuid' => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
          ],
        'user' =>
          [
            'uuids' =>
              [
                0 => '5391a8a9-d273-4f88-8114-7f884bbfe08b',
              ],
          ],
      ],
    '_links' => [
      'self' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications/1bd3487e-71d1-4fca-a2d9-5f969b3d35c1',
        ],
      'parent' =>
        [
          'href' => 'https://cloud.acquia.com/api/notifications',
        ],
    ],
  ],
];
