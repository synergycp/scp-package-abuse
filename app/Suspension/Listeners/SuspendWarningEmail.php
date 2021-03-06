<?php

namespace Packages\Abuse\App\Suspension\Listeners;

use App\Client\Server\ClientServerAccessService;
use App\Mail;
use App\Server\Server;
use Carbon\Carbon;
use Packages\Abuse\App\Suspension\Events;
use Packages\Abuse\App\Suspension\Suspension;

/**
 * Send out an Email when Server suspended.
 */
class SuspendWarningEmail
extends Mail\EmailListener
{
    /**
     * @var string
     */
    private $template = 'pkg/abuse/abuse_report_suspend_warning.tpl';

    /**
     * @var Suspension
     */
    private $suspension;

    /**
     * @var ClientServerAccessService
     */
    private $access;

    /**
     * SuspendWarningEmail constructor.
     *
     * @param Suspension                $suspension
     * @param ClientServerAccessService $access
     */
    public function boot(
        Suspension $suspension,
        ClientServerAccessService $access
    ) {
        $this->access = $access;
        $this->suspension = $suspension;
    }

    /**
     * Handle the event.
     *
     * @param Events\SuspensionEvent $event
     */
    public function handle(Events\SuspensionEvent $event)
    {
        $this->send(
            $event->server,
            $event->createdDate
        );
    }

    /**
     * @param Server $server
     * @param Carbon $createdDate
     */
    protected function send(Server $server, Carbon $createdDate)
    {
        $date = $this->suspension->maxReportDate();
        $days = $createdDate->diffInDays($date) + 1;
        $context = [
            'server' => $server->expose('name'),
            'report' => [
                'date' => $createdDate->toDateString(),
            ],
            'days' => $days,
        ];

        foreach ($this->access->clients($server) as $client) {
            $context['client'] = $client->expose('name');

            $this
                ->create($this->template)
                ->setData($context)
                ->toUser($client)
                ->send()
            ;
        }
    }
}
