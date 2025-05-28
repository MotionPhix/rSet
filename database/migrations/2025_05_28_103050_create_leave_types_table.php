<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('leave_types', function (Blueprint $table) {
      $table->id();
      $table->uuid('uuid')->unique();

      // Basic Info
      $table->string('name');
      $table->string('description')->nullable();
      $table->integer('days_allowed');

      // Add duration rules (Malawi-specific)
      $table->integer('min_duration')->default(1);
      $table->integer('max_duration')->nullable();
      $table->boolean('allow_custom_duration')->default(false);

      $table->string('gender')->nullable();
      $table->integer('min_employment_months')->default(0);

      $table->integer('cooldown_days')->default(0);
      $table->integer('max_usage_per_year')->nullable();

      $table->boolean('requires_approval')->default(true);
      $table->json('approvers')->nullable();

      // For sick leave tiers
      $table->integer('full_pay_days')->nullable();
      $table->integer('half_pay_days')->nullable();

      // Documentation
      $table->boolean('requires_documentation')->default(false);
      $table->string('documentation_type')->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('leave_types');
  }
};
