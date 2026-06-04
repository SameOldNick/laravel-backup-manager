<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('cleanup_schedules')->insertUsing(
                ['name', 'cron_expression', 'is_active', 'created_at', 'updated_at'],
                DB::table('backup_schedules')
                    ->select('name', 'cron_expression', 'is_active', 'created_at', 'updated_at')
                    ->where('command', 'cleanup')
            );

            DB::table('backup_schedules')
                ->where('command', 'cleanup')
                ->delete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::transaction(function (): void {
            DB::table('backup_schedules')->insertUsing(
                ['name', 'command', 'cron_expression', 'is_active', 'created_at', 'updated_at'],
                DB::table('cleanup_schedules')
                    ->select(
                        'name',
                        DB::raw("'cleanup' as command"),
                        'cron_expression',
                        'is_active',
                        'created_at',
                        'updated_at'
                    )
            );

            DB::table('cleanup_schedules')->delete();
        });
    }
};
