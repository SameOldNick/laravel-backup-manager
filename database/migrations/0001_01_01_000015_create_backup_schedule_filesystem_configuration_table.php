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
        Schema::create('backup_schedule_filesystem_configuration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_schedule_id')->constrained(indexName: 'backup_schedule_id_index')->cascadeOnDelete();
            $table->foreignId('filesystem_configuration_id')->constrained(indexName: 'filesystem_config_id_index')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_schedule_filesystem_configuration');
    }
};
