<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backup_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('path');
            $table->string('disk')->nullable();
            $table->string('name')->nullable();
            $table->nullableUuidMorphs('user');
            $table->nullableUuidMorphs('fileable');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['path', 'disk']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backup_files');
    }
};
