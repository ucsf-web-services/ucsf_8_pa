<?php

/**
 * @file
 * Contains mock objects of ACE database responses.
 */

return [
  0 =>
  (object) [
    'id' => '340107',
    'name' => 'example',
    'user_name' => 's111111',
    'password' => 'thepasswordispassword',
    'url' => 'mysqli://s111111:thepasswordispassword@127.0.0.1:3306/example7nm4yg7jcb',
    'ssh_host' => 'srv-0000.devcloud.hosting.acquia.com',
    'db_host' => '127.0.0.1',
    'flags' =>
    (object) [
      'default' => TRUE,
    ],
    'environment' =>
    (object) [
      'id' => '111111-11111111-c36a-401a-9724-fd8072a607d7',
      'name' => 'dev',
    ],
    '_links' =>
    (object) [
      'self' =>
      (object) [
        'href' => 'https://cloud.acquia.com/api/environments/111111-11111111-c36a-401a-9724-fd8072a607d7/databases/example',
      ],
    ],
  ],
];
