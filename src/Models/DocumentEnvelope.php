<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentEnvelope extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the model (e.g., User, System Process) that initiated this envelope.
     */
    public function creator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the individual document artifacts contained within this envelope.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}