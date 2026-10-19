<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('concepts')
            ->select(['id', 'crm_api_key'])
            ->whereNotNull('crm_api_key')
            ->orderBy('id')
            ->eachById(function (object $concept): void {
                try {
                    $apiKey = Crypt::decryptString($concept->crm_api_key);
                } catch (DecryptException) {
                    return;
                }

                DB::table('concepts')
                    ->where('id', $concept->id)
                    ->update(['crm_api_key' => $apiKey]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
