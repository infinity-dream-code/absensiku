<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'fungsi',
        'kpi_indikator',
    ];

    protected $casts = [
        'kpi_indikator' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role');
    }

    /**
     * @return array<int, string>
     */
    public function indikatorList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', (string) $this->fungsi))));
    }
}
