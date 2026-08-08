<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplateVersion extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'content' => 'array', // Assuming JSONB storage for Tiptap/Markdown blocks
    ];

    /**
     * Get the parent template container.
     */
    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    /**
     * Get the actual generated document artifacts that utilized this specific version.
     */
    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}