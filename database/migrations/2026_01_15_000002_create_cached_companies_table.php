<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cached_companies', function (Blueprint $table) {
            $table->id();

            $table->string('company_id', 20);
            $table->string('country_code', 2);

            $table->string('name', 500);
            $table->string('vat_id', 20)->nullable();
            $table->boolean('vat_payer')->nullable();

            $table->string('address_street', 255)->nullable();
            $table->string('address_house_number', 20)->nullable();
            $table->string('address_orientation_number', 20)->nullable();
            $table->string('address_zip', 10)->nullable();
            $table->string('address_city', 255)->nullable();

            $table->json('raw_response')->nullable();

            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['company_id', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cached_companies');
    }
};
