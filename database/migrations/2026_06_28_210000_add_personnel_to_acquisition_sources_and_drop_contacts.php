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
        // 1. Add columns to acquisition_sources
        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('source_type');
            $table->string('contact_position')->nullable()->after('contact_person');
        });

        // 2. Migrate data
        $contacts = DB::table('acquisition_contacts')->get();
        foreach ($contacts as $contact) {
            // Only update if not already set, to get the "first" contact
            $source = DB::table('acquisition_sources')->where('id', $contact->acquisition_source_id)->first();
            if ($source && empty($source->contact_person)) {
                DB::table('acquisition_sources')
                    ->where('id', $contact->acquisition_source_id)
                    ->update([
                        'contact_person' => $contact->name,
                        'contact_position' => $contact->position,
                    ]);
            }
        }

        // 3. Drop foreign key from asset_sources
        Schema::table('asset_sources', function (Blueprint $table) {
            $table->dropForeign(['acquisition_contact_id']);
            $table->dropColumn('acquisition_contact_id');
        });

        // 4. Drop acquisition_contacts table
        Schema::dropIfExists('acquisition_contacts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('acquisition_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acquisition_source_id')->nullable()->constrained('acquisition_sources')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::table('asset_sources', function (Blueprint $table) {
            $table->foreignId('acquisition_contact_id')->nullable()->constrained('acquisition_contacts')->nullOnDelete();
        });

        Schema::table('acquisition_sources', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'contact_position']);
        });
    }
};
