<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Syllabus Chapters
        Schema::create('syllabus_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('chapter_no')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('not_started'); // not_started, in_progress, completed
            $table->string('priority')->default('medium');    // low, medium, high
            $table->date('deadline_date')->nullable();
            $table->timestamps();
        });

        // 2. Homeworks
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high
            $table->string('status')->default('draft');    // draft, published
            $table->integer('max_marks')->default(100);
            $table->json('attachments')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 3. Homework Submissions
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->text('reply_note')->nullable();
            $table->json('files')->nullable();
            $table->string('status')->default('pending'); // pending, submitted, approved, revision_requested, late
            $table->string('grade')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homeworks');
        Schema::dropIfExists('syllabus_chapters');
    }
};
