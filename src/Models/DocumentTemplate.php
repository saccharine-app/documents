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

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the category this template belongs to.
     */
    public function documentCategory(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }
    
    /**
     * Get all temporal versions of this template.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentTemplateVersion::class);
    }
    
    /**
     * Helper to retrieve the currently active version based on temporal rules.
     */
    public function activeVersion(): ?DocumentTemplateVersion
    {
        return $this->versions()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('effective_from')
                      ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                      ->orWhere('effective_until', '>', now());
            })
            ->latest('version_number')
            ->first();
    }
}
