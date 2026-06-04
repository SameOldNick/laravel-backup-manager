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
        Schema::table('filesystem_configuration_sftp', function (Blueprint $table) {
            $table->string('root')
                ->nullable()
                ->after('port')
                ->comment('The root directory to use for the SFTP connection. This is optional and can be left empty to use the default root directory of the SFTP server.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filesystem_configuration_sftp', function (Blueprint $table) {
            $table->dropColumn('root');
        });
    }
};
