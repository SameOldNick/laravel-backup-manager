<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('disks');
            $table->integer('maximum_age_in_days')->nullable();
            $table->integer('maximum_storage_in_megabytes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_monitors');
    }
};
