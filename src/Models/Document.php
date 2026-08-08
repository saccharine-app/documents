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
     * Get the owning documentable model (e.g., AtNeedCase, PreNeedContract, etc.)
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function documentGenerationRun(): BelongsTo
    {
        return $this->belongsTo(DocumentGenerationRun::class, 'document_generation_run_id');
    }
}
