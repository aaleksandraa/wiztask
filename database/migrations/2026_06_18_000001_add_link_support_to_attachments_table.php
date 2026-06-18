<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('kind')->default('upload')->after('attachable_id');
            $table->text('external_path')->nullable()->after('path');
        });

        Setting::updateOrCreate(
            ['key' => 'allowed_file_types'],
            ['value' => '*'],
        );
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn(['kind', 'external_path']);
        });
    }
};
