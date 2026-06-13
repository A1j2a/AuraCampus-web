<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('font_family')->nullable()->after('logo_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_image')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('font_family');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_image');
        });
    }
};
