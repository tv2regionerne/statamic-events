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
            $table->boolean('enabled')->default(1)->after('config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_handlers', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
