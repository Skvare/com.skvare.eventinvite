<?php

use CRM_Eventinvite_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Eventinvite_Utils extends CRM_Eventinvite_Upgrader_Base {

  const customContactGroupName = 'Event_Invite';
  static $customGroup;
  static $customFields;

  public static function getCustomGroup() {

    if (empty(static::$customGroup)) {
      $params = [
        'extends' => 'Event',
        'is_active' => 1,
        'name' => self::customContactGroupName,
        'return' => ['id', 'table_name'],
      ];

      static::$customGroup = civicrm_api3('CustomGroup', 'getsingle', $params);

      unset(static::$customGroup['extends']);
      unset(static::$customGroup['is_active']);
      unset(static::$customGroup['name']);
    }

    return static::$customGroup;
  }

  /**
   * Get information about the custom Activity fields
   *
   * @return array Multi-dimensional, keyed by lowercased custom field
   *         name (i.e., civicrm_custom_group.name). Subarray keyed with id (i.e.,
   *         civicrm_custom_group.id), column_name, custom_n, and data_type.
   */
  public static function getCustomFields() {
    if (empty(static::$customFields)) {
      $custom_group = static::getCustomGroup();

      $params = [
        'custom_group_id' => $custom_group['id'],
        'is_active' => 1,
        'return' => ['id', 'column_name', 'name', 'data_type'],
      ];

      $fields = civicrm_api3('CustomField', 'get', $params);

      if (CRM_Utils_Array::value('count', $fields) < 1) {
        CRM_Core_Error::fatal('Event Invite - defined custom fields appear to be missing (custom field group' . self::customContactGroupName . ').');
      }

      foreach ($fields['values'] as $field) {
        static::$customFields[strtolower($field['name'])] = [
          'id' => $field['id'],
          'column_name' => $field['column_name'],
          'custom_n' => 'custom_' . $field['id'],
          'data_type' => $field['data_type'],
        ];
      }
    }

    return static::$customFields;
  }

  static function getRecipients($eventId) {
    $customFields = CRM_Eventinvite_Utils::getCustomFields();
    $event = self::getInviteeDetails($eventId, $customFields['event_invites']['custom_n']);
    $eventInvitees = [];

    if (!empty($event[$customFields['event_invites']['custom_n']])) {
      //support multiple values
      $groupIDs = (is_array($event[$customFields['event_invites']['custom_n']])) ?
        $event[$customFields['event_invites']['custom_n']] : explode(',', $event[$customFields['event_invites']['custom_n']]);
      foreach ($groupIDs as $groupID) {
        $eventInvitees = $eventInvitees + self::getEventInvitees($groupID);
      }
    }

    $eventInvitees = self::cleanInvitees($eventInvitees);

    return $eventInvitees;
  }

  /**
   * @param $eventId
   *
   * given an eventId, get the Event Invite Groups values
   */
  static function getInviteeDetails($eventId, $inviteField) {
    $invitees = [];
    try {
      $invitees = civicrm_api3('event', 'getsingle', [
        'id' => $eventId,
        'return' => [
          $inviteField,
          'title',
        ],
      ]);
      //Civi::log()->debug('_getInviteeDetails', array('invitees' => $invitees));
    }
    catch (CiviCRM_API3_Exception $e) {
    }

    return $invitees;
  }

  /**
   * @param $group_id
   * @return array
   *
   * given a groupID, get a list of all contacts
   * return id, name, email
   */
  static function getEventInvitees($group_id) {
    $invitees = [];
    try {
      $groupContactResult = civicrm_api3('GroupContact', 'get', [
        'sequential' => 1,
        'return' => ["contact_id"],
        'group_id' => $group_id,
      ]);

      $groupContacts = [];
      // make list of all contact ids
      $domainID = CRM_Core_Config::domainID();
      foreach ($groupContactResult['values'] as $entity) {
        $groupContacts[] = $entity['contact_id'];
      }

      return self::_getEventInviteesDetails($groupContacts);
    }
    catch (CiviCRM_API3_Exception $e) {
    }

    return $invitees;
  }

  static function _getEventInviteesDetails($groupContacts) {
    $invitees = [];
    try {
      $result = civicrm_api3('Contact', 'get', [
        'sequential' => 1,
        'return' => ["display_name", "email", "sort_name", "id"],
        'id' => ['IN' => $groupContacts],
        'do_not_email' => 0,
        'is_deleted' => 0,
        'is_deceased' => 0,
      ]);

      foreach ($result['values'] as $contact) {
        $invitees[$contact['contact_id']] = [
          'id' => $contact['contact_id'],
          'display_name' => $contact['display_name'],
          'sort_name' => $contact['sort_name'],
          'email' => $contact['email'],
        ];
      }
    }
    catch (CiviCRM_API3_Exception $e) {
    }

    return $invitees;
  }

  /**
   * @param $cids
   * @return array
   *
   * given a list of contact IDs, build array of values
   */
  static function getAdditionalInvitees($cids) {
    //Civi::log()->debug('_getAdditionalInvitees', array('$cids' => $cids));

    $invitees = [];
    foreach ($cids as $cid) {
      try {
        $contact = civicrm_api3('contact', 'getsingle', ['id' => $cid]);
        $invitees[$cid] = [
          'id' => $cid,
          'display_name' => $contact['display_name'],
          'sort_name' => $contact['sort_name'],
          'email' => $contact['email'],
          'current_employer' => $contact['current_employer'],
          'job_title' => $contact['job_title'],
        ];
      }
      catch (CiviCRM_API3_Exception $e) {
      }
    }

    return $invitees;
  }

  static function getRegistrants($eventId) {
    $registrants = [];

    try {
      $participants = civicrm_api3('participant', 'get', [
        'event_id' => $eventId,
        'options' => [
          'limit' => 0,
        ],
      ]);
      foreach ($participants['values'] as $participant) {
        //Civi::log()->debug('_getRegistrants', array('participant' => $participant));

        $email = civicrm_api3('contact', 'getvalue', [
          'id' => $participant['contact_id'],
          'return' => 'email',
        ]);

        $registrants[$participant['contact_id']] = [
          'display_name' => $participant['display_name'],
          'sort_name' => $participant['sort_name'],
          'email' => $email,
          'id' => $participant['contact_id'],
        ];
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      Civi::log()->debug('_getRegistrants', ['$e' => $e]);
    }

    return $registrants;
  }

  /**
   * @param $eventInvitees
   * @return array
   *
   * cycle through invitees and remove any where email is missing
   */
  static function cleanInvitees($eventInvitees) {
    foreach ($eventInvitees as $id => $eventInvitee) {
      if (empty($eventInvitee['email'])) {
        unset($eventInvitee[$id]);
      }
    }

    return $eventInvitees;
  }


  /**
   * get history of past notifications for display
   *
   * @param $eventId
   * @param $fromEmails
   * @return array
   */
  function getNotificationLog($eventId, $fromEmails) {
    $logs = [];
    $recipientOptions = [
      1 => 'All Invitees',
      2 => 'Unregistered Invitees',
      3 => 'Registered Contacts'
    ];
    if ($eventId) {
      $dao = CRM_Core_DAO::executeQuery("
        SELECT *
        FROM civicrm_eventinvite_notifications
        WHERE event_id = %1
      ", [
        1 => [$eventId, 'Positive']
      ]);

      while ($dao->fetch()) {
        $recipientsListURL = CRM_Utils_System::url('civicrm/event/inviteelist', "invitation_id={$dao->id}");
        $recipientsExportURL = CRM_Utils_System::url('civicrm/event/exportinvitees', "invitation_id={$dao->id}");
        $logs[] = [
          'event_id' => $dao->event_id,
          'recipients' => $dao->recipients,
          'recipients_value' => "
            <a href='{$recipientsListURL}' class='crm-popup'>{$recipientOptions[$dao->recipients]}</a>
            <a class='crm-hover-button' href='{$recipientsExportURL}' title='Export invitee list'>
              <i class='crm-i fa-download'></i>
            </a>
          ",
          'from_email' => $dao->from_email,
          'from_email_value' => $fromEmails[$dao->from_email],
          'msg_text' => $dao->msg_text,
          'contacts' => json_decode($dao->contacts),
          'notification_date' => date('m/d/Y H:i:s', strtotime($dao->notification_date)),
        ];
      }
    }

    return $logs;
  }

  /**
   * @param $values
   * @param $recipients
   *
   * store a record of the notification
   */
  function storeNotificationLog($values, $recipients) {
    CRM_Core_DAO::executeQuery("
      INSERT INTO civicrm_eventinvite_notifications
      (event_id, recipients, from_email, msg_text, contacts, notification_date)
      VALUES
      (%1, %2, %3, %4, %5, NOW())
    ", [
      1 => [$values['event_id'], 'Positive'],
      2 => [$values['recipients'], 'Positive'],
      3 => [$values['fromEmailAddress'], 'Positive'],
      4 => [$values['invitee_text'], 'String'],
      5 => [json_encode($recipients), 'String'],
    ]);
  }

  /**
   * @param $recipient
   * @param $values
   *
   * send email
   */
  function sendNotification($recipient, $values, $fromEmails) {
    $params = [
      'toName' => $recipient['display_name'],
      'toEmail' => $recipient['email'],
      'from' => htmlspecialchars_decode($fromEmails[$values['fromEmailAddress']]),
      'subject' => $values['event_title'],
      'html' => self::buildEmailContent($values['invitee_text'], $recipient['display_name'], $values['event_id']),
    ];
    //Civi::log()->debug('_sendNotification', array('params' => $params));

    if (Civi::settings()->get('event_invitee_notification')) {
      CRM_Utils_Mail::send($params);
    }
    else {
      CRM_Core_Error::debug_var('Event invite _sendNotifications', $params, TRUE, TRUE, 'invites');
    }
  }

  function buildEmailContent($text, $name, $eventId) {
    $text = nl2br($text);
    $date = date('F/j/Y');
    $url = CRM_Utils_System::url('civicrm/event/info', "reset=1&id={$eventId}", TRUE, NULL, TRUE, TRUE);
  }

}