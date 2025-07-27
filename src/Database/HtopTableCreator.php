<?php

namespace Htop\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HtopTableCreator
{
    public static function ensureTableExists()
    {
        $connection = config('htop.connection', 'htop_sqlite');

        if (!Schema::connection($connection)->hasTable('htop_requests')) {
            Schema::connection($connection)->create('htop_requests', function ($table) {
                $table->id();
                $table->string('method', 10);
                $table->string('path');
                $table->integer('status');
                $table->float('duration'); // ms
                $table->timestamp('timestamp');
            });
        }
    }
}
