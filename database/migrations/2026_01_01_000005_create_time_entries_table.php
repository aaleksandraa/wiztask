<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');
            $table->text('description')->nullable();
            $table->unsignedInteger('hours')->default(0);
            $table->unsignedInteger('minutes')->default(0);
            $table->unsignedInteger('total_minutes')->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->boolean('is_billable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
