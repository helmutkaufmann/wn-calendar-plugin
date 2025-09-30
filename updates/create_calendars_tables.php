<?php namespace Mercator\Calendar\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class CreateCalendarsTables extends Migration
{
    public function up()
    {
        Schema::create('mercator_calendar_calendars', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->index();
            $table->timestamps();
        });

        Schema::create('mercator_calendar_entries', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('calendar_id')->unsigned()->index()->nullable();
            $table->string('title');
            $table->string('summary')->nullable();
            $table->longText('description')->nullable();
            $table->dateTime('start_datetime'); 
            $table->dateTime('end_datetime')->nullable(); 
	    $table->string('timezone')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('published_from')->nullable();
            $table->dateTime('published_to')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mercator_calendar_entries');
        Schema::dropIfExists('mercator_calendar_calendars');
    }
}
