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
        Schema::table('leave_types', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('leave_types', 'display_name')) {
                $table->string('display_name')->after('name');
            }
            if (!Schema::hasColumn('leave_types', 'color')) {
                $table->string('color', 7)->default('#3b82f6')->after('documentation_type');
            }
            if (!Schema::hasColumn('leave_types', 'background_color')) {
                $table->string('background_color', 7)->default('#dbeafe')->after('color');
            }
            
            // Add unique constraint if it doesn't exist
            try {
                $table->unique(['company_id', 'name']);
            } catch (\Exception $e) {
                // Constraint might already exist, ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'color', 'background_color']);
            $table->dropUnique(['company_id', 'name']);
        });
    }
};
