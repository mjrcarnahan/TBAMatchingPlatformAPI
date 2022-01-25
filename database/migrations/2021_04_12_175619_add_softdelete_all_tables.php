<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftdeleteAllTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('types', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('sexes', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('maritals', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('question_types', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('options', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('sexes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('maritals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('question_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('memberships', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('options', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
