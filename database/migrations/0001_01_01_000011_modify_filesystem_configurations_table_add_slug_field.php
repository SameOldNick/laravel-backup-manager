<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('filesystem_configurations', function (Blueprint $table) {
            $table->string('slug')
                ->nullable()
                ->after('name');
        });

        $existingConfigurations = DB::table('filesystem_configurations')
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

        $usedSlugs = [];

        foreach ($existingConfigurations as $configuration) {
            $baseSlug = Str::slug($configuration->name);

            $slug = $baseSlug;
            $suffix = 2;

            while (isset($usedSlugs[$slug])) {
                $slug = "{$baseSlug}-{$suffix}";
                $suffix++;
            }

            DB::table('filesystem_configurations')
                ->where('id', $configuration->id)
                ->update(['slug' => $slug]);

            $usedSlugs[$slug] = true;
        }

        Schema::table('filesystem_configurations', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filesystem_configurations', function (Blueprint $table) {
            $table->dropUnique('filesystem_configurations_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
