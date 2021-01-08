<?php
use CRM_Eventinvite_ExtensionUtil as E;

/**
 * Eventinvite.Notification API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_eventinvite_Notification_spec(&$spec) {
  //$spec['magicword']['api.required'] = 1;
}

/**
 * Eventinvite.Notification API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_eventinvite_Notification($params) {
  $notifictionList = CRM_Eventinvite_Utils::getNotificationList();
  echo '<pre>'; print_r($notifictionList); exit;
  $mailingCount = 0;
  foreach ($notifictionList as $recipient) {
    $isSent = CRM_Eventinvite_Utils::sendNotification($recipient);
    if ($isSent) {
      CRM_Core_DAO::executeQuery("UPDATE civicrm_eventinvite_contact SET `status_id` = '1' WHERE id = {$recipient['nc_id']}");
      $mailingCount++;
    }
  }
  $returnValues[] = "{$mailingCount} Notifiction Email Sent.";

  return civicrm_api3_create_success($returnValues, $params, 'Eventinvite', 'Notification');
}
