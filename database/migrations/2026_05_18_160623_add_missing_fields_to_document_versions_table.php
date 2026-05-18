<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {

            $table->foreignId('user_id')
                ->nullable()
                ->after('document_id')
                ->constrained()
                ->nullOnDelete();

            $table->string('title')
                ->nullable()
                ->after('user_id');

        });
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {

            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'user_id',
                'title'
            ]);

        });
    }
};