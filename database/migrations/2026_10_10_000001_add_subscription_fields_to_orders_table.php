<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type')->default('catalog')->index()->after('user_id'); // catalog | regime
            $table->string('regime')->nullable()->index()->after('type');
            $table->string('pack')->nullable()->index()->after('regime');
            $table->string('plan')->nullable()->after('pack');
            $table->json('selection')->nullable()->after('plan');

            $table->string('parent_name')->nullable();
            $table->string('student_name')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('contact_email')->nullable();
            $table->text('customer_notes')->nullable();

            $table->text('admin_notes')->nullable();
            $table->string('platform_username')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('access_sent_at')->nullable();
        });

        // Reprise des demandes régime existantes (détails stockés jusqu'ici dans le paiement)
        DB::table('payments')
            ->where('provider', 'regime')
            ->orderBy('id')
            ->get(['order_id', 'provider_payload'])
            ->each(function ($payment) {
                $payload = json_decode((string) $payment->provider_payload, true) ?: [];
                $sel = $payload['selection'] ?? [];

                DB::table('orders')->where('id', $payment->order_id)->update([
                    'type'      => 'regime',
                    'regime'    => $payload['regime'] ?? null,
                    'pack'      => $sel['pack'] ?? null,
                    'plan'      => $sel['plan'] ?? null,
                    'selection' => json_encode($sel),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropIndex(['type']);
            $table->dropIndex(['regime']);
            $table->dropIndex(['pack']);
            $table->dropColumn([
                'type', 'regime', 'pack', 'plan', 'selection',
                'parent_name', 'student_name', 'phone', 'contact_email', 'customer_notes',
                'admin_notes', 'platform_username', 'approved_at', 'access_sent_at',
            ]);
        });
    }
};
