<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('concepts', function (Blueprint $table): void {
            $table->string('template_name')->nullable()->index();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->string('customer_id')->nullable()->index();
        });

        DB::table('concepts')->where('is_default', true)->update(['template_name' => config('app.default_concept')]);
        DB::table('concepts')->whereNull('template_name')->update(['template_name' => DB::raw('template')]);
        DB::table('users')->whereNotNull('crm_customer_id')->update(['customer_id' => DB::raw('crm_customer_id')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::table('concepts', function (Blueprint $table): void {
            $table->dropIndex(['template_name']);
            $table->dropColumn('template_name');
        });
    }
};
