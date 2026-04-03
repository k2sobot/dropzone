<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CleanupExpiredUploads;

Schedule::command(CleanupExpiredUploads::class)->hourly();