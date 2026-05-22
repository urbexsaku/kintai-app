<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionBreakRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_record_id',
        'requested_start_at',
        'requested_end_at',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(CorrectionRequet::class);
    }
}
