<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('novo')->index();
            $table->string('priority')->default('normalan')->index();
            $table->date('task_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('billing_type')->default('po_satu');
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('fixed_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->boolean('is_billable')->default(true)->index();
            $table->string('payment_status')->default('za_naplatu')->index();
            $table->text('internal_note')->nullable();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
