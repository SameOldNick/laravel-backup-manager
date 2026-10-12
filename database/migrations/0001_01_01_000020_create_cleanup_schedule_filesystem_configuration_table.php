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
        Schema::create('cleanup_schedule_filesystem_configuration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleanup_schedule_id')->constrained(indexName: 'cleanup_sched_fs_cfg_schedule_fk')->cascadeOnDelete();
            $table->foreignId('filesystem_configuration_id')->constrained(indexName: 'cleanup_sched_fs_cfg_filesystem_fk')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleanup_schedule_filesystem_configuration');
    }
};
