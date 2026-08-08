<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends Model
{
    use HasUuids;

    protected $guarded = [];

    // Cast the JSONB content to a PHP array automatically
    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];
    
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }
}
