<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaestrasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('description',100);
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->string('description',100);
        });

        Schema::create('sexes', function (Blueprint $table) {
            $table->id();
            $table->string('description',20);
        });

        Schema::create('maritals', function (Blueprint $table) {
            $table->id();
            $table->string('description',100);
        });

        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('description',30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_types');
        Schema::dropIfExists('maritals');
        Schema::dropIfExists('sexes');
        Schema::dropIfExists('types');
        Schema::dropIfExists('roles');
    }
}
