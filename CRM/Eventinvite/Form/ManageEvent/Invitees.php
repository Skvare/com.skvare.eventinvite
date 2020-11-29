<?php

use CRM_Eventinvite_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eventinvite_Form_ManageEvent_Invitees extends CRM_Event_Form_ManageEvent {
  public $_emails;
  public $_fromEmails;
  public $_recipientOptions;
  public $_id;

  public function preProcess() {
    parent::preProcess();

    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE, NULL, 'GET');
    $this->assign('eventId', $this->_id);
    $this->assign('id', $this->_id);

    if ($this->_id) {
      $this->_doneUrl = CRM_Utils_System::url(CRM_Utils_System::currentPath(), "action=update&reset=1&id={$this->_id}");
    }
  }

  public function buildQuickForm() {
    //determine if we need to expose these tools
    //$eventId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $eventId = $this->_id;
    $customFields = CRM_Eventinvite_Utils::getCustomFields();
    $invitees = CRM_Eventinvite_Utils::getInviteeDetails($eventId, $customFields['event_invites']['custom_n']);
    if (!empty($invitees[$customFields['event_invites']['custom_n']])) {
      $this->assign('enableInvitees', TRUE);
    }

    $descriptions = [];

    // add form elements
    $this->add('hidden', 'event_id', $eventId);
    $this->add('hidden', 'event_title', $invitees['title']);

    $options = $this->_recipientOptions = [
      1 => 'All Invitees',
      2 => 'Unregistered Invitees',
      3 => 'Registered Contacts'
    ];
    $this->addRadio('recipients', ts('Recipients'), $options, ['allowClear' => FALSE]);

    //build from address options
    $fromAddresses = civicrm_api3('OptionValue', 'get', [
      'options' => ['limit' => 0],
      'sequential' => 1,
      'option_group_id' => "from_email_address",
      'domain_id' => CRM_Core_Config::domainID(),
    ]);
    $this->_fromEmails = [];
    foreach ($fromAddresses['values'] as $fromAddress) {
      $this->_fromEmails[$fromAddress['id']] = htmlspecialchars($fromAddress['label']);
    }
    $contactID = CRM_Core_Session::getLoggedInContactID();
    $contactName = civicrm_api3('contact', 'getvalue', ['id' => $contactID, 'return' => 'sort_name']);
    $contactEmails = CRM_Core_BAO_Email::allEmails($contactID);
    foreach ($contactEmails as $contactEmail) {
      $this->_fromEmails[$contactEmail['id']] = htmlspecialchars("\"{$contactName}\" <{$contactEmail['email']}>");
    }
    asort($this->_fromEmails);
    $this->add('select', 'fromEmailAddress', ts('From'), $this->_fromEmails, TRUE, ['class' => 'crm-select2 huge']);
    /*Civi::log()->debug('buildQuickForm', array(
      '$this->_fromEmails' => $this->_fromEmails,
      '$contactEmails' => $contactEmails,
    ));*/

    $this->add(
      'textarea', // field type
      'invitee_text', // field name
      'Email Text', // field label
      ['rows' => 10, 'cols' => 60], // list of attributes
      TRUE // is required
    );
    $descriptions['invitee_text'] = 'Enter the text for the body of the email. The content will be inserted into the standard Event Invite email template with the greeting "Dear Full Name:". In addition, a link to the event information page will be appended to the email body.';

    $notificationLog = CRM_Eventinvite_Utils::getNotificationLog($eventId, $this->_fromEmails);
    $this->assign('notificationLog', $notificationLog);

    $this->setDefaults([
      'recipients' => 1,
    ]);

    //export all invitees
    $fullExportURL = CRM_Utils_System::url('civicrm/event/exportinvitees', "event_id={$eventId}");
    $fullExportHTML = "
      <a class='crm-hover-button action-item' href='{$fullExportURL}'><i class='crm-i fa-download'></i> Export All Invitees</a>
    ";
    $this->assign('fullExport', $fullExportHTML);

    //export unregistered invitees
    $unregExportURL = CRM_Utils_System::url('civicrm/event/exportinvitees', "event_id={$eventId}&type=unregistered");
    $unregExportHTML = "<a class='crm-hover-button action-item' href='{$unregExportURL}'><i class='crm-i fa-download'></i> Export Unregistered Invitees</a>";
    $this->assign('unregExport', $unregExportHTML);
    $this->assign('descriptions', $descriptions);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => ts('Send Invitation'),
        'isDefault' => TRUE,
      ],
    ]);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * Add local and global form rules.
   */
  public function addRules() {
    $this->addFormRule(['CRM_Eventinvite_Form_ManageEvent_Invitees', 'formRule']);
  }

  /**
   * Global validation rules for the form.
   *
   * @param array $values
   *   Posted values of the form.
   *
   * @return array
   *   list of errors to be posted back to the form
   */
  public static function formRule($values) {
    $errors = [];
    if (empty($values['invitee_text'])) {
      $errors['invitee_text'] = 'Please enter text for the email message.';
    }

    return empty($errors) ? TRUE : $errors;
  }

  public function postProcess() {
    $values = $this->exportValues();
    //Civi::log()->debug('postProcess', array('$values' => $values));

    $recipients = [];

    switch ($values['recipients']) {
      case 1:
        //get all invitees
        $recipients = CRM_Eventinvite_Utils::getRecipients($values['event_id'], $values['recipients']);
        break;

      case 2:
        //get all invitees + registrants, return array_diff
        $invitees = CRM_Eventinvite_Utils::getRecipients($values['event_id'], $values['recipients']);
        $registrants = CRM_Eventinvite_Utils::getRegistrants($values['event_id']);
        $recipients = array_diff_key($invitees, $registrants);
        break;

      case 3:
        //get registrants
        $recipients = CRM_Eventinvite_Utils::getRegistrants($values['event_id']);
        break;

      default:
    }
    //Civi::log()->debug('postProcess', array('$recipients' => $recipients));

    //cycle through recipients and send email
    foreach ($recipients as $recipient) {
      CRM_Eventinvite_Utils::sendNotification($recipient, $values, $this->_fromEmails);
    }

    //store log
    CRM_Eventinvite_Utils::storeNotificationLog($values, $recipients);

    parent::endPostProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }

    return $elementNames;
  }



}
