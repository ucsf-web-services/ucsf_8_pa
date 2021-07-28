<?php

/**
 * @file
 * Mock Response for ACE DB Backup Requests.
 */

return [
  (object) [
    'id' => 1,
    'database' =>
      [
        'id' => 14,
        'name' => 'db_name',
      ],
    'type' => 'daily',
    'started_at' => '2012-05-15T12:00:00Z',
    'completed_at' => '2012-05-15T12:00:00Z',
    'flags' =>
      [
        'deleted' => FALSE,
      ],
    'environment' =>
      [
        'id' => '1-a47ac10b-58cc-4372-a567-0e02b2c3d470',
        'name' => 'Production',
      ],
    '_links' =>
      [
        'self' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/1',
          ],
        'parent' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases',
          ],
        'download' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/1/actions/download',
          ],
      ],
  ],
  (object) [
    'id' => 2,
    'database' =>
      [
        'id' => 14,
        'name' => 'db_name',
      ],
    'type' => 'daily',
    'started_at' => '2012-03-28T12:00:00Z',
    'completed_at' => '2012-03-28T12:00:01Z',
    'flags' =>
      [
        'deleted' => FALSE,
      ],
    'environment' =>
      [
        'id' => '1-a47ac10b-58cc-4372-a567-0e02b2c3d470',
        'name' => 'Production',
      ],
    '_links' =>
      [
        'self' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/2',
          ],
        'parent' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases',
          ],
        'download' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/2/actions/download',
          ],
      ],
  ],
  (object) [
    'id' => 3,
    'database' =>
      [
        'id' => 14,
        'name' => 'db_name',
      ],
    'type' => 'daily',
    'started_at' => '2017-01-08T04:00:00Z',
    'completed_at' => '2017-01-08T04:00:01Z',
    'flags' =>
      [
        'deleted' => FALSE,
      ],
    'environment' =>
      [
        'id' => '1-a47ac10b-58cc-4372-a567-0e02b2c3d470',
        'name' => 'Production',
      ],
    '_links' =>
      [
        'self' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/3',
          ],
        'parent' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases',
          ],
        'download' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/3/actions/download',
          ],
      ],
  ],
  (object) [
    'id' => 4,
    'database' =>
      [
        'id' => 14,
        'name' => 'db_name',
      ],
    'type' => 'daily',
    'started_at' => '2017-01-08T05:00:02Z',
    'completed_at' => '2017-01-08T05:00:03Z',
    'flags' =>
      [
        'deleted' => FALSE,
      ],
    'environment' =>
      [
        'id' => '1-a47ac10b-58cc-4372-a567-0e02b2c3d470',
        'name' => 'Production',
      ],
    '_links' =>
      [
        'self' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/4',
          ],
        'parent' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases',
          ],
        'download' =>
          [
            'href' => 'https://cloud.acquia.com/api/environments/1-a47ac10b-58cc-4372-a567-0e02b2c3d470/databases/db_name/backups/4/actions/download',
          ],
      ],
  ],
];
