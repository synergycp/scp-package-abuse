{extends "email:base_alert.tpl"}

{block "alert-type"}danger{/block}

{block "alert-message"}Abuse Report Received!{/block}

{block "body"}
    <tr>
        <td class="content-block">
            Greetings {$client.name},
        </td>
    </tr>
    <tr>
        <td class="content-block">
            We have received a report of abuse against your server{if $server}, {$server.name|escape}{/if}. Please respond to the report at the link below within 48 hours to avoid service suspension.
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <b>Date Received:</b> {$report.date}
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <a href="{$urls.view}" class="btn-primary">
                View Report
            </a>
        </td>
    </tr>
    <tr>
        <td class="content-block">
            <p>SPAM, DMCA complaints, and DDoS will not be tolerated on our network.</p>
            <p>If you do not know how this activity is occurring on your server, we recommend reinstalling your operating system immediately and using a strong root password, then restricting authentication to SSH keys only.</p>
        </td>
    </tr>
{/block}
