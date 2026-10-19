<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('concepts', function (Blueprint $table) {
            $table->foreignId('crm_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        DB::table('concepts')
            ->whereNotNull('crm_api_url')
            ->where('crm_api_url', '!=', '')
            ->orderBy('id')
            ->eachById(function (object $concept): void {
                $apiUrl = rtrim((string) $concept->crm_api_url, '/');
                $crmId = DB::table('crms')->where('api_url', $apiUrl)->value('id');

                if (! $crmId) {
                    $crmId = DB::table('crms')->insertGetId([
                        'name' => parse_url($apiUrl, PHP_URL_HOST) ?: 'CRM',
                        'api_url' => $apiUrl,
                        'is_enabled' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('concepts')->where('id', $concept->id)->update(['crm_id' => $crmId]);
            });

        Schema::table('concepts', function (Blueprint $table) {
            $table->dropColumn('crm_api_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('concepts', function (Blueprint $table) {
            $table->string('crm_api_url')->nullable()->after('domain_name');
        });

        DB::table('concepts')->orderBy('id')->eachById(function (object $concept): void {
            $apiUrl = DB::table('crms')->where('id', $concept->crm_id)->value('api_url');

            DB::table('concepts')->where('id', $concept->id)->update(['crm_api_url' => $apiUrl]);
        });

        Schema::table('concepts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('crm_id');
        });
    }
};
