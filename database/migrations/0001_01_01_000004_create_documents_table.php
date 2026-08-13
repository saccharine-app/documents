<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuidMorphs('documentable'); 
            
            $table->foreignUuid('document_template_version_id')->constrained('document_template_versions')->restrictOnDelete();
            $table->foreignUuid('document_envelope_id')->nullable()->constrained('document_envelopes')->nullOnDelete();
            
            $table->enum('status', ['draft', 'generated', 'signed', 'voided']);
            
            // Storage references
            $table->string('generated_file_path')->nullable();
            $table->string('signed_file_path')->nullable();
            
            // Snapshot of data/context at generation time for audit purposes
            $table->jsonb('metadata')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};