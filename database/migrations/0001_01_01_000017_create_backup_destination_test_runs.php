<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SameOldNick\BackupManager\Enums\RunStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backup_destination_test_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('filesystem_configuration_id')->constrained()->cascadeOnDelete();
            $table->enum('status', RunStatus::cases())->default(RunStatus::Pending->value);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_destination_test_runs');
    }
};
