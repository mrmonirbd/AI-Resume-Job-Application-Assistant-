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
        Schema::create('concepts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain_name')->unique();
            $table->string('crm_api_url')->nullable();
            $table->text('crm_api_key')->nullable();
            $table->string('crm_service_id')->nullable();
            $table->string('product_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('template')->default('resume-assistant');
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('concept_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('crm_customer_id')->nullable()->after('career_goal')->index();
            $table->string('crm_subscription_id')->nullable()->after('crm_customer_id')->index();
            $table->string('crm_web_user_id')->nullable()->after('crm_subscription_id')->index();
            $table->string('domain_name')->nullable()->after('crm_web_user_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('concept_id');
            $table->dropIndex(['crm_customer_id']);
            $table->dropIndex(['crm_subscription_id']);
            $table->dropIndex(['crm_web_user_id']);
            $table->dropIndex(['domain_name']);
            $table->dropColumn(['crm_customer_id', 'crm_subscription_id', 'crm_web_user_id', 'domain_name']);
        });

        Schema::dropIfExists('concepts');
    }
};
