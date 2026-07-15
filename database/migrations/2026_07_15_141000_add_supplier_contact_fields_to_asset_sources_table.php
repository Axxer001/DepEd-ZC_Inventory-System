<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->string('supplier_personnel')->nullable()->after('contact_position');
            $table->string('supplier_contact_number')->nullable()->after('supplier_personnel');
            $table->string('supplier_contact_email')->nullable()->after('supplier_contact_number');
            $table->string('supplier_service_center')->nullable()->after('supplier_contact_email');
        });

        // Copy existing supplier info from suppliers into asset_sources for existing records
        $assetSources = DB::table('asset_sources')->get();
        foreach ($assetSources as $asrc) {
            if ($asrc->supplier_id) {
                $supplier = DB::table('suppliers')->where('id', $asrc->supplier_id)->first();
                if ($supplier) {
                    DB::table('asset_sources')->where('id', $asrc->id)->update([
                        'supplier_personnel' => $supplier->supplier_personnel,
                        'supplier_contact_number' => $supplier->contact_number,
                        'supplier_contact_email' => $supplier->contact_email,
                        'supplier_service_center' => $supplier->service_center,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropColumn([
                'supplier_personnel',
                'supplier_contact_number',
                'supplier_contact_email',
                'supplier_service_center'
            ]);
        });
    }
};
