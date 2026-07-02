<?php use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    // No-op: items table did not yet exist at this point in a fresh install.
    // category_id and nullable columns are handled in the create migration or restructure.
    public function up(): void { if (!Schema::hasTable('items') || Schema::hasColumn('items', 'category_id')) return;
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->nullable()->after('name');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->bigInteger('school_id')->unsigned()->nullable()->change();
            $table->bigInteger('user_id')->unsigned()->nullable()->change();
        });
    }
    public function down(): void { if (!Schema::hasTable('items') || !Schema::hasColumn('items', 'category_id')) return;
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']); $table->dropColumn('category_id');
        });
    }
};
