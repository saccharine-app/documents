<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_envelopes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Identifies what triggered the envelope (e.g., the User or System ID)
            $table->uuidMorphs('creator'); 
            
            $table->enum('status', ['generating', 'pending_signatures', 'executed', 'failed'])->default('generating');
            
            // For routing back to external e-sign platforms
            $table->string('esign_driver')->nullable(); // e.g., 'zoho', 'docusign'
            $table->string('external_envelope_id')->nullable()->index(); 
            
            $table->jsonb('metadata')->nullable(); // Recipients, routing order, etc.
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_envelopes');
    }
};