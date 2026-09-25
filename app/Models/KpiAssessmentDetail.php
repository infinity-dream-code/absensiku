<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiAssessmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kpi_assessment',
        'nama_indikator',
        'bobot',
        'skor',
        'nilai_akhir',
    ];

    protected $casts = [
        'bobot' => 'integer',
        'skor' => 'float',
        'nilai_akhir' => 'float',
    ];

    public function assessment()
    {
        return $this->belongsTo(KpiAssessment::class, 'id_kpi_assessment');
    }
}
