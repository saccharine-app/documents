<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_template_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('document_template_id')->constrained('document_templates')->cascadeOnDelete();
            
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('version_number');
            
            // Temporal mapping
            $table->timestamp('effective_from')->nullable();
            $table->timestamp('effective_until')->nullable();
            
            // Layout and Logic
            $table->enum('type', ['tiptap_block', 'fillable_pdf', 'blade_view']);
            $table->string('dto_class')->nullable(); // e.g., 'App\Http\Resources\Documents\StandardContractResource'
            $table->jsonb('content')->nullable(); // For Tiptap/Markdown
            $table->string('file_path')->nullable(); // For fillable PDFs
            $table->string('view_name')->nullable(); // For Blade views
            
            $table->timestamps();
            
            // Ensure we don't have duplicate version numbers within a template
            $table->unique(['document_template_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_versions');
    }
};