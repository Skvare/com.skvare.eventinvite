{* HEADER *}
<h3>Invitee Notifications</h3>

{if $enableInvitees}
  <p>Use the tool below to send an invitation email to event invitees.</p>
    {* FIELD EXAMPLE: OPTION 1 (AUTOMATIC LAYOUT) *}

    {foreach from=$elementNames item=elementName}
      <div class="crm-section">
        <div class="label">{$form.$elementName.label}</div>
        <div class="content">
            {if $descriptions.$elementName}<span class="description">{$descriptions.$elementName}</span><br />{/if}
            {$form.$elementName.html}
        </div>
        <div class="clear"></div>
      </div>
    {/foreach}

  <div class="crm-section">
    <div class="label"></div>
    <div class="content">
        {$fullExport}
        {$unregExport}
    </div>
    <div class="clear"></div>
  </div>

    {*display notification log history*}
    {if $notificationLog}
      <table class="selector row-highlight">
        <tr>
          <th>Date</th>
          <th>Recipient Option</th>
          <th>From Email</th>
          <th>Message Text</th>
        </tr>
          {foreach from=$notificationLog item=row}
            <tr>
              <td>{$row.notification_date}</td>
              <td>{$row.recipients_value}</td>
              <td>{$row.from_email_value}</td>
              <td>{$row.msg_text}</td>
            </tr>
          {/foreach}
      </table>
    {/if}

    {* FOOTER *}
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{else}
  <p>You have not selected any individuals to invite to this event. Please visit the Info and Settings tab to choose your invitees, then return to this tab to generate an invitation email.</p>
{/if}
