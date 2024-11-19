<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleAndDescriptionToTasksTable extends Migration
{
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('title');       // Add the 'title' column
            $table->text('description');   // Add the 'description' column
        });
    }

    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('title');       // Remove the 'title' column
            $table->dropColumn('description'); // Remove the 'description' column
        });
    }
}
