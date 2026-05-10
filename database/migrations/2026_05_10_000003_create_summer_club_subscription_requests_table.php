<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summer_club_subscription_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('parent_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pack_key');
            $table->string('pack_name');
            $table->json('selected_subjects')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('duration_months')->default(3);
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('pack_key');
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summer_club_subscription_requests');
    }
};
