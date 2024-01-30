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
        Schema::table('event_handlers', function (Blueprint $table) {
            $table->json('events')->after('event');
        });

        Handler::all()
            ->each(function ($handler) {
                if (! $handler->event) {
                    return;
                }

                $handler->events = [$handler->event];
                $handler->save();
            });

        Schema::table('event_handlers', function (Blueprint $table) {
            $table->dropColumn('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_handlers', function (Blueprint $table) {
            $table->string('event')->nullable()->after('events');
        });

        Handler::all()
            ->each(function ($handler) {
                if (! $handler->events) {
                    return;
                }

                $handler->event = $handler->events[0];
                $handler->save();
            });

        Schema::table('event_handlers', function (Blueprint $table) {
            $table->dropColumn('events');
        });
    }
};
