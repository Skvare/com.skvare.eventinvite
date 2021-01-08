<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:Eventinvite.Notification',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Eventinvite.Notification API',
      'description' => 'Call Eventinvite.Notification API',
      'run_frequency' => 'Always',
      'api_entity' => 'Eventinvite',
      'api_action' => 'Notification',
      'parameters' => '',
    ],
  ],
];
