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
        // 1. Create procurement_modes table
        Schema::create('procurement_modes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Create acquisition_contacts table
        Schema::create('acquisition_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acquisition_source_id')->nullable()->constrained('acquisition_sources')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 3. Add FK columns to asset_sources
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->foreignId('procurement_mode_id')->nullable()->constrained('procurement_modes')->nullOnDelete();
            $table->foreignId('acquisition_contact_id')->nullable()->constrained('acquisition_contacts')->nullOnDelete();
        });

        // 4. Data Migration
        // Process existing asset_sources to populate new tables and link them
        $assetSources = DB::table('asset_sources')->get();
        foreach ($assetSources as $source) {
            // Handle Mode
            $modeId = null;
            if (!empty($source->mode_of_acquisition)) {
                $mode = DB::table('procurement_modes')->where('name', trim($source->mode_of_acquisition))->first();
                if (!$mode) {
                    $modeId = DB::table('procurement_modes')->insertGetId([
                        'name' => trim($source->mode_of_acquisition),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $modeId = $mode->id;
                }
            }

            // Handle Contact
            $contactId = null;
            if (!empty($source->source_personnel) || !empty($source->personnel_position)) {
                $contact = DB::table('acquisition_contacts')
                    ->where('acquisition_source_id', $source->acquisition_source_id)
                    ->where('name', trim($source->source_personnel))
                    ->where('position', trim($source->personnel_position))
                    ->first();

                if (!$contact) {
                    $contactId = DB::table('acquisition_contacts')->insertGetId([
                        'acquisition_source_id' => $source->acquisition_source_id,
                        'name' => trim($source->source_personnel) ?: null,
                        'position' => trim($source->personnel_position) ?: null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $contactId = $contact->id;
                }
            }

            // Update asset_sources row
            DB::table('asset_sources')->where('id', $source->id)->update([
                'procurement_mode_id' => $modeId,
                'acquisition_contact_id' => $contactId,
            ]);
        }

        // 5. Drop old columns
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropColumn(['mode_of_acquisition', 'source_personnel', 'personnel_position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore old columns
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->string('mode_of_acquisition')->nullable();
            $table->string('source_personnel')->nullable();
            $table->string('personnel_position')->nullable();
        });

        // Data Migration Down (Lossy due to structure change, but we try)
        $assetSources = DB::table('asset_sources')->get();
        foreach ($assetSources as $source) {
            $modeName = null;
            if ($source->procurement_mode_id) {
                $mode = DB::table('procurement_modes')->where('id', $source->procurement_mode_id)->first();
                $modeName = $mode ? $mode->name : null;
            }

            $contactName = null;
            $contactPos = null;
            if ($source->acquisition_contact_id) {
                $contact = DB::table('acquisition_contacts')->where('id', $source->acquisition_contact_id)->first();
                if ($contact) {
                    $contactName = $contact->name;
                    $contactPos = $contact->position;
                }
            }

            DB::table('asset_sources')->where('id', $source->id)->update([
                'mode_of_acquisition' => $modeName,
                'source_personnel' => $contactName,
                'personnel_position' => $contactPos,
            ]);
        }

        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropForeign(['procurement_mode_id']);
            $table->dropForeign(['acquisition_contact_id']);
            $table->dropColumn(['procurement_mode_id', 'acquisition_contact_id']);
        });

        Schema::dropIfExists('acquisition_contacts');
        Schema::dropIfExists('procurement_modes');
    }
};
