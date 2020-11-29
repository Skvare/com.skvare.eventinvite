<?php

require_once 'eventinvite.civix.php';
// phpcs:disable
use CRM_Eventinvite_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function eventinvite_civicrm_config(&$config) {
  _eventinvite_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function eventinvite_civicrm_xmlMenu(&$files) {
  _eventinvite_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function eventinvite_civicrm_install() {
  _eventinvite_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function eventinvite_civicrm_postInstall() {
  _eventinvite_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function eventinvite_civicrm_uninstall() {
  _eventinvite_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function eventinvite_civicrm_enable() {
  _eventinvite_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function eventinvite_civicrm_disable() {
  _eventinvite_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function eventinvite_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _eventinvite_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function eventinvite_civicrm_managed(&$entities) {
  _eventinvite_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function eventinvite_civicrm_caseTypes(&$caseTypes) {
  _eventinvite_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function eventinvite_civicrm_angularModules(&$angularModules) {
  _eventinvite_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function eventinvite_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _eventinvite_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function eventinvite_civicrm_entityTypes(&$entityTypes) {
  _eventinvite_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function eventinvite_civicrm_themes(&$themes) {
  _eventinvite_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function eventinvite_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function eventinvite_civicrm_navigationMenu(&$menu) {
//  _eventinvite_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _eventinvite_civix_navigationMenu($menu);
//}

function eventinvite_civicrm_fieldOptions($entity, $field, &$options, $params) {
  if ($entity == 'Event') {
    $customFields = CRM_Eventinvite_Utils::getCustomFields();
    if ($field == $customFields['event_invites']['custom_n']) {
      $options = CRM_Core_PseudoConstant::nestedGroup();
    }
  }
}


function eventinvite_civicrm_tabset($tabsetName, &$tabs, $context) {
  /*Civi::log()->debug('', array(
    'tabsetName' => $tabsetName,
    'tabs' => $tabs,
    'context' => $context,
  ));*/

  if ($tabsetName == 'civicrm/event/manage' && isset($context['event_id'])) {
    $eventID = $context['event_id'];
    $url = CRM_Utils_System::url('civicrm/event/manage/invitees',
      "reset=1&action=update&component=event&id=$eventID" );
    //add a new tab along with url
    $tabs['invitees'] = array(
      'title' => ts('Invitations'),
      'link' => $url,
      'valid' => 1,
      'active' => 1,
      'current' => false,
    );
  }
}


function eventinvite_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Event_Form_Registration_Register' && _eventinvite_isRSVP($form->_eventId)) {
    $form->assign('isRSVP', TRUE);
    CRM_Core_Resources::singleton()->addScriptFile('com.skvare.eventinvite', 'js/rsvp.js');

    $options = $form->_rsvpOptions = [
      1 => 'Yes, I plan to attend the event.',
      0 => 'No, I will not be able to attend.',
    ];
    $form->addRadio('rsvp', ts('RSVP'), $options, ['allowClear' => FALSE], '<br />', TRUE);
    CRM_Core_Region::instance('form-body')->add([
      'template' => 'CRM/Eventinvite/RSVP.tpl',
    ]);
  }
  if (in_array($formName, [
      'CRM_Event_Form_Registration_Confirm',
      'CRM_Event_Form_Registration_ThankYou',
    ]) &&
    _eventinvite_isRSVP($form->_eventId)
  ) {
    $form->assign('isRSVP', TRUE);
    $params = $form->getVar('_params');

    //if RSVP not attending
    if (empty($params[0]['rsvp'])) {
      CRM_Core_Resources::singleton()->addScriptFile('com.skvare.eventinvite', 'js/rsvp_confirm.js');
      CRM_Core_Resources::singleton()->addStyleFile('com.skvare.eventinvite', 'css/rsvp.css');

      //workaround to prevent error if using simple fee options and rsvp = no
      $form->_priceSetId = NULL;
    }
  }

}

function eventinvite_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {

  if ($formName == 'CRM_Event_Form_Registration_Register') {
    $eventID = $form->_eventId;

    $customFields = CRM_Eventinvite_Utils::getCustomFields();
    $invitees = CRM_Eventinvite_Utils::getInviteeDetails($eventID, $customFields['event_invites']['custom_n']);
    // check invite group present  OR RSVP enabled ?
    if (!empty($invitees[$customFields['event_invites']['custom_n']]) || _eventinvite_isRSVP($eventID)) {
      // contact must be logged in for invite validatation.
      $session = CRM_Core_Session::singleton();
      $cid = $session->get('userID');
      $qf = ($form->_submitValues['qfKey']) ? "&qfKey={$form->_submitValues['qfKey']}" : '';
      $url = CRM_Utils_System::url('civicrm/event/register', "id={$eventID}{$qf}");

      if (empty($cid)) {
        CRM_Core_Error::statusBounce('You must be logged in and part of the invite list in order to register for this event.', $url);
      }

      $allowed = FALSE;
      // check contact present in invite list
      if (!empty($invitees[$customFields['event_invites']['custom_n']])) {
        $resultContact = civicrm_api3('Contact', 'getsingle', [
          'return' => ["group"],
          'id' => $cid,
        ]);
        // is group resent in contact result ?
        if (!empty($resultContact['groups'])) {
          $groupContact = explode(',', $resultContact['groups']);
          // check contact group are present of invite lists
          $isGroupPresent = array_intersect($invitees[$customFields['event_invites']['custom_n']], $groupContact);
          if (empty($isGroupPresent)) {
            $errors[$key] = ts("You must be part of the invite list in order to register for this event.");
            CRM_Core_Error::statusBounce('You must be part of the invite list in order to register for this event.', $url);
          }
        }
      }
    }
  }

  // IF RSVP is No, then remove pricsing vadation and unset amount.
  if (in_array($formName, ['CRM_Event_Form_Registration_Register', 'CRM_Event_Form_Registration_Confirm'])
    && _eventinvite_isRSVP($form->_eventId)
  ) {

    //cycle through and remove required validation on all price fields
    if (array_key_exists('rsvp', $fields) && empty($fields['rsvp'])) {
      foreach ($form->_priceSet['fields'] as $fid => $val) {
        $form->setElementError('price_' . $fid, NULL);
        $fields['price_' . $fid] = NULL;
      }

      $form->_lineItem = [];
      $form->setElementError('_qf_default', NULL);
    }
  }

  // Do not allow multiple participant functionality on configuration page if RSVP is enabled.
  if ($formName == 'CRM_Event_Form_ManageEvent_Registration') {
    if ($fields['is_multiple_registrations'] && _eventinvite_isRSVP($form->_id)) {
      $errors['is_multiple_registrations'] = 'You cannot enable the multiple participants option if you have restricted access to an invite list. In order to verify the person is allowed to register for the event, we must have a single logged in person completing the form at a time.';
    }
  }
}

function eventinvite_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Event_Form_Registration_Confirm' && _eventinvite_isRSVP($form->_eventId)) {
    $params = $form->getVar('_params');
    $participantID = $form->getVar('_participantId');

    //if RSVP not attending, adjust participant status
    if (empty($params['rsvp']) && !empty($participantID)) {
      $statusTypes = CRM_Event_PseudoConstant::participantStatus();
      try {
        $resultParticipant = civicrm_api3('Participant', 'getsingle', [
          'return' => ["contact_id", "event_id", "id"],
          'id' => $participantID,
        ]);
        $resultParticipant['status_id'] = array_search('No-show', $statusTypes);
        civicrm_api3('participant', 'create', $resultParticipant);
      }
      catch (CRM_API3_Exception $e) {
      }
    }
  }
}

function _eventinvite_isRSVP($eventID) {
  try {
    $rsvp = civicrm_api3('custom_value', 'get', [
      'entity_table' => 'civicrm_event',
      'entity_id' => $eventID,
    ]);

    //return custom field RSVP value
    $rsvpFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('enable_rsvp_workflow', 'Event_Invite');

    return ($rsvp['values'][$rsvpFieldId]['latest']);
  }
  catch (CRM_API3_Exception $e) {
  }

  return FALSE;
}

function _eventinvite_isInvite($eventID) {
  try {
    $rsvp = civicrm_api3('custom_value', 'get', [
      'entity_table' => 'civicrm_event',
      'entity_id' => $eventID,
    ]);

    //return custom field RSVP value
    $inviteFieldId = CRM_Core_BAO_CustomField::getCustomFieldID('event_invites', 'Event_Invite');

    return ($rsvp['values'][$inviteFieldId]['latest']);

    $customFields = CRM_Eventinvite_Utils::getCustomFields();
    $invitees = CRM_Eventinvite_Utils::getInviteeDetails($eventId, $customFields['event_invites']['custom_n']);
    if (!empty($invitees[$customFields['event_invites']['custom_n']])) {

    }
  }
  catch (CRM_API3_Exception $e) {
  }

  return FALSE;
}
