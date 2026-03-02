<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'archive')) {
                $table->boolean('archive')->default(false)->after('message');
            }
            if (!Schema::hasColumn('contacts', 'lu')) {
                $table->boolean('lu')->default(false)->after('archive');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['archive', 'lu']);
        });
    }
};