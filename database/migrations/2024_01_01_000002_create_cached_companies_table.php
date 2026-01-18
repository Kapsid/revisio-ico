<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cached Companies Table Migration
 *
 * This table stores company data fetched from registries.
 * Data is cached to reduce API calls to external registries.
 *
 * Design Decisions:
 *
 * 1. COMPOSITE UNIQUE KEY (company_id, country_code)
 *    - Same company ID can exist in different countries
 *    - This ensures uniqueness per country
 *
 * 2. VERSIONING (version column)
 *    - Data is not overwritten, new versions are created
 *    - Allows tracking historical changes
 *    - Useful for audit trails and debugging
 *
 * 3. SOFT DELETES
 *    - Data is never truly deleted
 *    - Maintains history for compliance
 *
 * 4. INDEXED COLUMNS
 *    - company_id: Primary lookup field
 *    - country_code: Filtered queries
 *    - fetched_at: For cache expiration checks
 *    - is_current: For quick current version lookup
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cached_companies', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Company identification
            $table->string('company_id', 20)->comment('IČO/REGON/NIP');
            $table->string('country_code', 2)->comment('cz, sk, pl');

            // Versioning - data is not overwritten
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current')->default(true)->comment('Whether this is the latest version');

            // Company data (matching API response structure)
            $table->string('name', 500);
            $table->string('vat_id', 20)->nullable()->comment('DIČ/VAT ID');
            $table->boolean('vat_payer')->nullable();

            // Address fields
            $table->string('address_street', 255)->nullable();
            $table->string('address_house_number', 20)->nullable();
            $table->string('address_orientation_number', 20)->nullable();
            $table->string('address_zip', 10)->nullable();
            $table->string('address_city', 255)->nullable();

            // Raw response for debugging/audit
            $table->json('raw_response')->nullable()->comment('Original registry response');

            // Timestamps
            $table->timestamp('fetched_at')->comment('When data was fetched from registry');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['company_id', 'country_code']);
            $table->index(['country_code', 'is_current']);
            $table->index('fetched_at');

            // Unique constraint for current version per company/country
            $table->unique(['company_id', 'country_code', 'is_current'], 'unique_current_company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cached_companies');
    }
};
