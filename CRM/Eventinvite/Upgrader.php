<?php
use CRM_Eventinvite_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Eventinvite_Upgrader extends CRM_Eventinvite_Upgrader_Base {

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    $smarty = CRM_Core_Smarty::singleton();
    $smarty->assign('eventinvite_custom_group_name', CRM_Eventinvite_Utils::customContactGroupName);

    $customIDs = $this->findCustomGroupValueIDs();
    $smarty->assign('customIDs', $customIDs);
    $this->executeCustomDataTemplateFile('eventinvite-customdata.xml.tpl');
    $this->installEventInviteMsgWorkflowTpls();

    $this->executeSqlFile('sql/event_invite_notification.sql');
  }

  public function installEventInviteMsgWorkflowTpls() {
    try {
      $optionGroup = civicrm_api3('OptionGroup', 'create', [
        'name' => 'msg_tpl_workflow_eventinvite',
        'title' => ts("Message Template Workflow for Event Invite Email", ['domain' => 'com.skvare.eventinvite']),
        'description' => ts("Message Template Workflow for Invite Email", ['domain' => 'com.skvare.eventinvite']),
        'is_reserved' => 0,
        'is_active' => 1,
      ]);
      $optionGroupId = $optionGroup['id'];
    }
    catch (Exception $e) {
      // if an exception is thrown, most likely the option group already exists,
      // in which case we'll just use that one
      $optionGroupId = civicrm_api3('OptionGroup', 'getvalue', [
        'name' => 'msg_tpl_workflow_eventinvite',
        'return' => 'id',
      ]);
    }

    $msgTpls = [
      [
        'description' => ts('Event Invite - Receipt.', ['domain' => 'com.skvare.eventinvite']),
        'label' => ts('Event Invite - Receipt', ['domain' => 'com.skvare.eventinvite']),
        'name' => 'eventinvite_receipt',
        'subject' => ts("Event Invite", ['domain' => 'com.skvare.eventinvite']),
      ],
    ];

    $this->create_msg_tpl($msgTpls, $optionGroupId);
  }

  function create_msg_tpl($msgTpls, $optionGroupId) {
    $msgTplDefaults = [
      'is_active' => 1,
      'is_default' => 1,
      'is_reserved' => 0,
    ];

    $baseDir = CRM_Extension_System::singleton()->getMapper()->keyToBasePath('com.skvare.eventinvite') . '/';
    foreach ($msgTpls as $i => $msgTpl) {
      $optionValue = civicrm_api3('OptionValue', 'create', [
        'description' => $msgTpl['description'],
        'is_active' => 1,
        'is_reserved' => 1,
        'label' => $msgTpl['label'],
        'name' => $msgTpl['name'],
        'option_group_id' => $optionGroupId,
        'value' => ++$i,
        'weight' => $i,
      ]);
      $txt = file_get_contents($baseDir . 'msg_tpl/' . $msgTpl['name'] . '.txt');
      $html = file_get_contents($baseDir . 'msg_tpl/' . $msgTpl['name'] . '.html');

      $params = array_merge($msgTplDefaults, [
        'msg_title' => $msgTpl['label'],
        'msg_subject' => $msgTpl['subject'],
        'msg_text' => $txt,
        'msg_html' => $html,
        'workflow_id' => $optionValue['id'],
      ]);
      civicrm_api3('MessageTemplate', 'create', $params);
    }

  }

  public function executeCustomDataTemplateFile($relativePath) {
    $smarty = CRM_Core_Smarty::singleton();
    $xmlCode = $smarty->fetch($relativePath);
    $xml = simplexml_load_string($xmlCode);

    require_once 'CRM/Utils/Migrate/Import.php';
    $import = new CRM_Utils_Migrate_Import();
    $import->runXmlElement($xml);

    return TRUE;
  }

  public function findCustomGroupValueIDs() {
    $result = [];

    $query = "SELECT `table_name`, `AUTO_INCREMENT` FROM `information_schema`.`TABLES`
      WHERE `table_schema` = DATABASE()
      AND `table_name` IN ('civicrm_custom_group', 'civicrm_custom_field')";
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $result[$dao->table_name] = (int)$dao->AUTO_INCREMENT;
    }

    return $result;
  }

}
