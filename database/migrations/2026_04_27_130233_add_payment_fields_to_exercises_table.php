<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (! Schema::hasColumn('exercises', 'is_paid')) {
                $table->boolean('is_paid')->default(true)->after('is_published');
            }

            if (! Schema::hasColumn('exercises', 'price_cents')) {
                $table->integer('price_cents')->default(0)->after('is_paid');
            }

            if (! Schema::hasColumn('exercises', 'currency')) {
                $table->string('currency', 10)->default('TND')->after('price_cents');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exercises', function (Blueprint $table) {
            if (Schema::hasColumn('exercises', 'currency')) {
                $table->dropColumn('currency');
            }

            if (Schema::hasColumn('exercises', 'price_cents')) {
                $table->dropColumn('price_cents');
            }

            if (Schema::hasColumn('exercises', 'is_paid')) {
                $table->dropColumn('is_paid');
            }
        });
    }
};