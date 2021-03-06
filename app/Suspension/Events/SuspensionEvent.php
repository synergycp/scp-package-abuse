<?php

namespace Packages\Abuse\App\Suspension\Events;

use App\Support\Event;
use App\Support\Database\SerializesModels;
use App\Server\Server;
use Carbon\Carbon;

abstract class SuspensionEvent extends Event
{
    use SerializesModels;

    /**
     * @var Server
     */
    public $server;

    /**
     * Created date
     *
     * @var Carbon
     */
    public $createdDate;

    /**
     * Create a new event instance.
     *
     * @param Server $server
     * @param Carbon $createdDate
     */
    public function __construct(Server $server, Carbon $createdDate)
    {
        $this->server = $server;
        $this->createdDate = $createdDate;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
