<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the owning model this document is attached to (e.g., AtNeedCase, CareProfile).
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the specific historical template version used to generate this artifact.
     */
    public function templateVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateVersion::class, 'document_template_version_id');
    }

    /**
     * Get the envelope this document belongs to, if it was generated as part of a bundle.
     */
    public function envelope(): BelongsTo
    {
        return $this->belongsTo(DocumentEnvelope::class, 'document_envelope_id');
    }

    /* public function documentGenerationRun(): BelongsTo
    {
        return $this->belongsTo(DocumentGenerationRun::class, 'document_generation_run_id');
    } */    
}
