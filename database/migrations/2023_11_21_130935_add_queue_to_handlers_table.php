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
        Schema::table('event_handlers', function (Blueprint $table) {
            $table->boolean('should_queue')->default(0)->after('config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_handlers', function (Blueprint $table) {
            $table->dropColumn('should_queue');
        });
    }
};
