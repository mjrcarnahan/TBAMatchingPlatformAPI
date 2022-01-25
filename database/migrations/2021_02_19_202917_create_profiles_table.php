<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('types');
            $table->integer('question_position')->default(0);
            $table->string('nickname')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('state_old')->nullable();
            $table->string('city_old')->nullable();
            $table->foreignId('marital_id')->nullable()->constrained('maritals');
            $table->boolean('agree')->default(0);
            $table->string('picture')->nullable();
            $table->string('obgyn_file')->nullable();
            $table->string('credit_file')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->onDelete('set null')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });


        Schema::dropIfExists('profiles');
    }
}
