<?php
use CRM_Eventinvite_ExtensionUtil as E;

class CRM_Eventinvite_Page_InviteeList extends CRM_Core_Page {
  public function run() {
    $invitationID = CRM_Utils_Request::retrieve('invitation_id', 'Positive', $this, TRUE);

    $contacts = CRM_Core_DAO::singleValueQuery("
      SELECT c.id, c.display_name, c.sort_name, e.email
      FROM civicrm_eventinvite_contact ec
      INNER JOIN civicrm_contact c ON (c.id = ec.contact_id)
      LEFT JOIN civicrm_email e ON (e.contact_id = c.id)
      WHERE ec.id = %1
    ", [
      1 => [$invitationID, 'Positive']
    ]);

    $contacts = json_decode($contacts);
    $list = [];
    while ($dao->fetch()) {
      $list[] = [
        'id' => $dao->id,
        'name' => $dao->display_name,
        'email' => $dao->email,
        'sort_name' => (!empty($dao->sort_name)) ? $dao->sort_name : $dao->display_name,
      ];
    }

    //sort by last name
    usort($list, function ($item1, $item2) {
      if ($item1['sort_name'] == $item2['sort_name'])
        return 0;

      return $item1['sort_name'] < $item2['sort_name'] ? -1 : 1;
    });

    $this->assign('recipientsList', $list);

    parent::run();
  }
}