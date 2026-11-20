<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->foreignId('subcategory_id')
                ->nullable()
                ->after('category_id')
                ->constrained('categories')
                ->restrictOnDelete();
        });

        DB::table('categories')
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->get(['id', 'parent_id'])
            ->each(function (object $subcategory): void {
                DB::table('listings')
                    ->where('category_id', $subcategory->id)
                    ->update([
                        'category_id' => $subcategory->parent_id,
                        'subcategory_id' => $subcategory->id,
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('listings')
            ->whereNotNull('subcategory_id')
            ->orderBy('id')
            ->get(['id', 'subcategory_id'])
            ->each(function (object $listing): void {
                DB::table('listings')
                    ->where('id', $listing->id)
                    ->update([
                        'category_id' => $listing->subcategory_id,
                        'subcategory_id' => null,
                    ]);
            });

        Schema::table('listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
        });
    }
};
