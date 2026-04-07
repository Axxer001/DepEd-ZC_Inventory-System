<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration B: Add classification columns to stakeholders.
     *
     * source_type   — For Distributors: Government | Contractor | Donor | NGO | Other
     * entity_type   — For Recipients: School | District | Division | Individual | External
     * position      — For Individual recipients: their job title/position (e.g. "Principal")
     * person_name   — Separate field for the actual person's name, keeping org names clean in 'name'
     *
     * Design logic:
     *   - For ORGANIZATIONS (schools, LGUs, companies): name = organization name, person_name = null
     *   - For INDIVIDUALS (a specific teacher or officer): name = full name, person_name = same value,
     *     position = their role, entity_type = Individual
     */
    public function up(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            // For distinguishing funding channels on Distributors
            $table->enum('source_type', ['Government', 'Contractor', 'Donor', 'NGO', 'Other'])
                  ->nullable()
                  ->after('type');

            // For distinguishing the nature of Recipients
            $table->enum('entity_type', ['School', 'District', 'Division', 'Individual', 'External'])
                  ->default('School')
                  ->after('source_type');

            // The position/role of an individual recipient (e.g. "Principal", "District Supervisor")
            $table->string('position')->nullable()->after('entity_type');

            // For individual people: stores the person's actual name separately from the org name
            $table->string('person_name')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'entity_type', 'position', 'person_name']);
        });
    }
};
