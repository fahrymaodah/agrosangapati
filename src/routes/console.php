<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// SCHEDULED BACKUP COMMANDS (ADD-004)
// ============================================================

// Run full backup daily at 2:00 AM
Schedule::command('backup:run')->daily()->at('02:00');

// Clean up old backups daily at 3:00 AM
Schedule::command('backup:clean')->daily()->at('03:00');

// Monitor backup health daily at 4:00 AM
Schedule::command('backup:monitor')->daily()->at('04:00');
