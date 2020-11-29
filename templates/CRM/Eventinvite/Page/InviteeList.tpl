{if $recipientsList}
    <table class="selector row-highlight">
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
        {foreach from=$recipientsList item=row}
            <tr>
                <td>{$row.name}</td>
                <td>{$row.email}</td>
            </tr>
        {/foreach}
    </table>
{/if}
