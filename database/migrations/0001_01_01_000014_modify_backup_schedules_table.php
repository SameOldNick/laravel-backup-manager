<?php

use SameOldNick\BackupManager\Enums\BackupTypes;
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
        Schema::table('backup_schedules', function (Blueprint $table) {
            $table->dropColumn('command');
            $table->string('type')
                ->default(BackupTypes::Full->value)
                ->after('name')
                ->comment('The type of backup to perform.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('command')->after('name')->default('backup')->comment('The command to execute, either backup or cleanup.');
        });
    }
};
