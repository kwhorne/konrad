<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VatReportAttachment extends Model
{
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'vat_report_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function vatReport(): BelongsTo
    {
        return $this->belongsTo(VatReport::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    protected static function booted(): void
    {
        static::deleting(function (VatReportAttachment $attachment) {
            Storage::delete($attachment->path);
        });
    }
}
