<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tv2regionerne\StatamicEvents\Models\Handler;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_handler_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Handler::class);
            $table->string('event');
            $table->text('input')->nullable();
            $table->text('output')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_handler_executions');
    }
};
