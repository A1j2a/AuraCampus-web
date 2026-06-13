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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('user_name')->nullable()->unique()->after('last_name');
            $table->integer('user_type')->default(0)->after('user_name'); // e.g. 1 = Admin, 2 = Teacher, 3 = Student, 4 = Parent
            $table->string('device_info')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_os_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'user_name',
                'user_type',
                'device_info',
                'device_type',
                'device_os_version'
            ]);
        });
    }
};
