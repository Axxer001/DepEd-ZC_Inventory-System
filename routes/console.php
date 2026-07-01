<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Inspire command removed to resolve PHPStan Closure binding issue

Schedule::command('app:check-asset-lifecycle')->daily();
Schedule::command('app:auto-return-borrows')->daily();
