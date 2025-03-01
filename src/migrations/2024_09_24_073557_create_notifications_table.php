<?php

use AbdullahMateen\LaravelHelpingMaterial\Enums\Notification\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->default(null)->index();
            $table->foreignId('receiver_id')->index();

            $table->nullableMorphs('notifiable');

            $table->string('type')->nullable()->default(null);
            $table->string('title')->nullable()->default(null);
            $table->text('body')->nullable();
            $table->json('data')->nullable()->default(null);

            $table->boolean('status')->nullable()->default(StatusEnum::Unread->value);
            $table->dateTime('send_at')->nullable()->default(null);
            $table->json('exception')->nullable()->default(null);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
