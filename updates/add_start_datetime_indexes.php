<?php namespace Mercator\Calendar\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class AddStartDatetimeIndexes extends Migration
{
    public function up()
    {
        Schema::table('mercator_calendar_entries', function ($table) {
            // single-column index for sorting/filtering by start date
            $table->index('start_datetime', 'mcal_entries_start_datetime_idx');

            // composite index for queries within one calendar ordered by start
            $table->index(['calendar_id', 'start_datetime'], 'mcal_entries_calendar_start_idx');
            $table->dateTime('start_datetime')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('mercator_calendar_entries', function ($table) {
            $table->dropIndex('mcal_entries_start_datetime_idx');
            $table->dropIndex('mcal_entries_calendar_start_idx');
        });
  
    }
}

