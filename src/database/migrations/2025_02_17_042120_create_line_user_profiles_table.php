<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('line_user_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('verify_code', config('line.verify_code_length', 4))->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('line_user_id', 33)->unique();
            $table->string('profile', 2048)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->dateTime('unfollowed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('line_bot_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('type');
            $table->string('webhook_event_id', 26)->nullable();
            $table->uuid('request_id')->nullable();
            $table->unsignedSmallInteger('request_status')->default(200);
            $table->unsignedBigInteger('line_user_profile_id')->index();
            $table->foreign('line_user_profile_id')->references('id')->on('line_user_profiles');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->jsonb('payload');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->isProduction()) {
            return;
        }

        Schema::dropIfExists('line_bot_chat_logs');
        Schema::dropIfExists('line_user_profiles');
    }
};
