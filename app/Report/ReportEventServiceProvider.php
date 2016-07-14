<?php

namespace Packages\Abuse\App\Report;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

/**
 * Setup Abuse Report Event Listeners.
 */
class ReportEventServiceProvider extends EventServiceProvider
{
    public function listens()
    {
        return require dirname(__FILE__).'/Listeners.php';
    }
}
