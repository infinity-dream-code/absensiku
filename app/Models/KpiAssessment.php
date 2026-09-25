<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_penilai',
        'id_role',
        'bulan',
        'tahun',
        'skor_akhir',
        'kategori',
        'rekomendasi',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'skor_akhir' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'id_penilai');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role');
    }

    public function details()
    {
        return $this->hasMany(KpiAssessmentDetail::class, 'id_kpi_assessment');
    }
}
