<?php

namespace Saccharine\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentGenerationRun extends Model
{
    use HasUuids;

    protected $fillable = [
        'care_profile_id',
        'document_category_id',
        'user_id',
        'status',
        'output_preference',
        'configuration',
    ];

    public function careProfile(): BelongsTo
    {
        return $this->belongsTo(CareProfile::class, 'care_profile_id');
    }

    public function userId(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
