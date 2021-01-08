<?php
use CRM_Eventinvite_ExtensionUtil as E;

class CRM_Eventinvite_Page_ExportInvitees extends CRM_Core_Page {
  public function run() {
    $invitationID = CRM_Utils_Request::retrieve('invitation_id', 'Positive');
    $eventID = CRM_Utils_Request::retrieve('event_id', 'Positive');
    $type = CRM_Utils_Request::retrieve('type', 'String');

    if (empty($invitationID) && empty($eventID)) {
      $url = CRM_Utils_System::url('civicrm/event/manage', 'reset=1');
      CRM_Core_Error::statusBounce(ts("Unable to retrieve notification details."), $url);
    }

    if ($invitationID) {
      $rows = $this->getNotificationInvitees($invitationID);
      $this->generateExport("Invitation{$invitationID}", $rows);
    }
    elseif ($eventID) {
      $exportType = ($type == 'unregistered') ? $type : 'all';
      $rows = $this->getEventInvitees($eventID, $exportType);
      $this->generateExport("Event{$eventID}_{$exportType}", $rows);
    }

    CRM_Utils_System::civiExit();
  }

  /**
   * @param $invitationID
   * @return array
   */
  function getNotificationInvitees($invitationID) {
    $sqlQuery = "SELECT en.id, en.event_id, ec.contact_id, c.display_name, e.email
      FROM civicrm_eventinvite_notifications en 
      INNER JOIN civicrm_eventinvite_contact ec ON (en.id = ec.notification_id)
      INNER JOIN civicrm_contact c ON (c.id = ec.contact_id)
      INNER JOIN civicrm_email e ON (e.contact_id = c.id and e.is_primary = 1) 
      WHERE en.id = %1";
    $dao = CRM_Core_DAO::executeQuery($sqlQuery, [1 => [$invitationID, 'Positive']]);
    $list = [];
    while ($dao->fetch()) {
      $contactDetails = civicrm_api3('contact', 'getsingle', ['id' => $dao->contact_id]);
      $list[] = [
        'id' => $dao->contact_id,
        'display_name' => $dao->display_name,
        'sort_name' => $contactDetails['sort_name'],
        'email' => $dao->email,
        'current_employer' => $contactDetails['current_employer'],
        'job_title' => $contactDetails['job_title'],
      ];
    }
    //sort by last name
    usort($list, function ($item1, $item2) {
      if ($item1['sort_name'] == $item2['sort_name'])
        return 0;

      return $item1['sort_name'] < $item2['sort_name'] ? -1 : 1;
    });

    return $list;
  }

  function getEventInvitees($eventID, $type = 'all') {
    $rows = CRM_Eventinvite_Utils::getRecipients($eventID);
    //Civi::log()->debug('getEventInvitees', array('$rows' => $rows));

    if ($type == 'unregistered') {
      $registrants = CRM_Eventinvite_Utils::getRegistrants($eventID);
      $rows = array_diff_key($rows, $registrants);
    }

    return $rows;
  }

  function generateExport($id, $rows) {
    $headerRows = [
      'id' => 'ID',
      'display_name' => 'Full Name',
      'sort_name' => 'Sort Name',
      'email' => 'Email',
    ];

    $date = date('YmdHis');
    CRM_Core_Report_Excel::writeCSVFile("Event_Invitees_{$id}_{$date}",
      $headerRows, $rows, NULL, TRUE);
  }
}
