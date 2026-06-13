<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sections table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name'); // A, B, C
            $table->timestamps();
        });

        // 2. Fix classes table - add capacity, section_id
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('name')->constrained('sections')->nullOnDelete();
            $table->integer('capacity')->default(40)->after('section');
            $table->string('academic_year')->nullable()->after('capacity');
            $table->boolean('is_active')->default(true)->after('academic_year');
        });

        // 3. Fix student_details - add blood_group, status
        Schema::table('student_details', function (Blueprint $table) {
            $table->string('blood_group')->nullable()->after('gender');
            $table->string('status')->default('active')->after('blood_group'); // active, inactive, transferred, graduated
        });

        // 4. Fix teacher_details - add experience
        Schema::table('teacher_details', function (Blueprint $table) {
            $table->string('experience')->nullable()->after('qualification'); // e.g. "5 years"
            $table->boolean('is_active')->default(true)->after('joining_date');
        });

        // 5. Parent details table
        Schema::create('parent_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('relation')->nullable(); // Father, Mother, Guardian
            $table->string('occupation')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
        });

        // 6. Teacher class section assignments
        Schema::create('teacher_class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->boolean('is_class_teacher')->default(false);
            $table->timestamps();

            $table->unique(['teacher_id', 'class_id']);
        });

        // 7. Documents table (polymorphic)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->morphs('documentable'); // teacher, student, parent
            $table->string('type'); // photo, aadhar, pan, birth_certificate, transfer_certificate, medical, address_proof, certificate
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        // 8. User credentials table
        Schema::create('user_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username')->unique();
            $table->string('plain_password'); // encrypted
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_credentials');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('teacher_class_sections');
        Schema::dropIfExists('parent_details');

        Schema::table('teacher_details', function (Blueprint $table) {
            $table->dropColumn(['experience', 'is_active']);
        });

        Schema::table('student_details', function (Blueprint $table) {
            $table->dropColumn(['blood_group', 'status']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn(['section_id', 'capacity', 'academic_year', 'is_active']);
        });

        Schema::dropIfExists('sections');
    }
};
