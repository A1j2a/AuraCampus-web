<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. School Events (calendar)
        Schema::create('school_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('general'); // exam, ptm, holiday, general
            $table->date('event_date');
            $table->string('event_time')->nullable();
            $table->string('organizer')->nullable();
            $table->string('organizer_avatar_url')->nullable();
            $table->string('banner_image_url')->nullable();
            $table->timestamps();
        });

        // 2. PTC (Parent-Teacher Conference) Bookings
        Schema::create('ptc_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
            $table->string('term');
            $table->date('ptc_date');
            $table->string('time_slot');
            $table->string('status')->default('booked'); // booked, completed, cancelled
            $table->timestamps();
        });

        // 3. Add attachments column to student_leave_requests
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('student_leave_requests', function (Blueprint $table) {
            $table->dropColumn('attachments');
        });
        Schema::dropIfExists('ptc_bookings');
        Schema::dropIfExists('school_events');
    }
};
