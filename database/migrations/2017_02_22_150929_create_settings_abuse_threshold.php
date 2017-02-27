<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Support\Database\Migration;
use App\Setting\Setting;

class CreateSettingsAbuseThreshold extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $group = $this->addSettingGroup('Abuse Reports');
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.report.threshold', [
            'validator' => Setting::VALID_INT,
            'value' => 7,
        ]);

        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.host', [
            'value' => 'imap.gmail.com',
        ]);
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.user', [
            'validator' => Setting::VALID_EMAIL,
            'value' => 'abuse.test.usd@gmail.com',
        ]);
        $this->addSetting($group, Setting::TYPE_TEXT, 'pkg.abuse.auth.pass', [
            'value' => '#Abuse123!',
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->deleteSettingGroup('Abuse Reports');
    }
}
