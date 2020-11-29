<?php
use CRM_Eventinvite_ExtensionUtil as E;

class CRM_Eventinvite_Page_InviteeList extends CRM_Core_Page {
  public function run() {
    $invitationID = CRM_Utils_Request::retrieve('invitation_id', 'Positive', $this, TRUE);

    $contacts = CRM_Core_DAO::singleValueQuery("
      SELECT contacts
      FROM civicrm_eventinvite_notifications
      WHERE id = %1
    ", [
      1 => [$invitationID, 'Positive']
    ]);

    $contacts = json_decode($contacts);
    $list = [];

    foreach ($contacts as $cid => $contact) {
      $list[] = [
        'id' => $cid,
        'name' => $contact->display_name,
        'email' => $contact->email,
        'sort_name' => (!empty($contact->sort_name)) ? $contact->sort_name : $contact->display_name,
      ];
    }

    //sort by last name
    usort($list, function ($item1, $item2) {
      if ($item1['sort_name'] == $item2['sort_name'])
        return 0;

      return $item1['sort_name'] < $item2['sort_name'] ? -1 : 1;
    });

    $this->assign('recipientsList', $list);

    /*Civi::log()->debug('', array(
      'invitationID' => $invitationID,
      '$contacts' => $contacts,
      '$list' => $list,
    ));*/

    parent::run();
  }
}