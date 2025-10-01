<?php

use Filament\Jetstream\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            if (!Schema::hasColumn('teams', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });

        // Auto-generate slug for existing teams (Seeder will loop through teams in the Seeder)
        Team::whereNull('slug')->chunk(100, function ($teams) {
            foreach ($teams as $team) {
                $baseSlug = Str::slug($team->name);
                $slug = $baseSlug;
                $counter = 1;

                // check until slug is unique
                while (
                    Team::where('slug', $slug)
                        ->where('id', '!=', $team->id) // excluding the current team being processed
                        ->exists()
                ) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }

                $team->slug = $slug;
                $team->save();
            }
        });

        // Add a unique index to the 'slug' column to prevent duplicate values.
        Schema::table('teams', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
