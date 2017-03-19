<?php

namespace Packages\Abuse\App\Report\Suspension\Listeners;

use Packages\Abuse\App\Report\Suspension\Events;
use App\Server\Server;
use App\Mail;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\Suspension;

/**
 * Send out an Email when Server suspended.
 */
class SuspendWarningEmail
    extends Mail\EmailListener
{

    private $suspension;

    /**
     * @param Mail\Mailer   $mail
     * @param Suspension    $suspension
     */
    public function __construct(
        Mail\Mailer $mail,
        Suspension $suspension
    ) {
        parent::__construct($mail);

        $this->suspension = $suspension;
    }

    /**
     * Handle the event.
     *
     * @param Events\ServerSuspend $event
     */
    public function handle(Events\ServerSuspendWarning $event)
    {
        $server = $event->server;
        $createdDate = $event->createdDate;
        $this->send($server, $createdDate);
    }

    /**
     * @param Server $server, Report created_at, pkg_abuse_auto_suspension
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $date = $date = $this->suspension->maxReportDate();
        $days = $createdDate->diffInDays($date);

        $client = $server->access->client;
        $context = [
            'client' => $client->expose('name'),
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString()
            ],
            'days' => $days
        ];

        $this
            ->create('abuse_report_suspended.tpl')
            ->setData($context)
            ->toUser($client)
            ->send()
        ;
    }
}
