<?php

use App\Enums\AiExtractionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drafts produced by the AI PDF parser.
     *
     * The JSON stored here is a *suggestion* only. Nothing in this table is
     * used by search, the datacenter or stats; data only reaches the real
     * study tables when a user reviews and saves the normal forms.
     */
    public function up(): void
    {
        Schema::create('ai_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_id')->nullable()->constrained()->nullOnDelete();

            // Where the PDF lives (Storage disk + path), and its original name.
            $table->string('disk')->default('local');
            $table->string('pdf_path');
            $table->string('original_filename')->nullable();

            $table->string('status')->default(AiExtractionStatus::Queued->value);
            $table->string('schema_version');
            $table->string('model')->nullable();

            // Same shape JsonIngester accepts (title, summary, investigators, ...).
            $table->jsonb('extracted')->nullable();
            // Per-field { page, quote, confidence } so users can check the AI's work.
            $table->jsonb('evidence')->nullable();
            // Token counts etc. from the API response, for keeping an eye on cost.
            $table->jsonb('usage')->nullable();

            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_extractions');
    }
};
