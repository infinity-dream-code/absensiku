<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferensiWfo extends Model
{
    use HasFactory;

    protected $table = 'referensi_wfo';

    protected $fillable = [
        'custid',
        'type',
        'hari',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'custid');
    }

    public function getHariArrayAttribute(): array
    {
        if (!$this->hari) {
            return [];
        }

        $decoded = json_decode($this->hari, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($item) => is_string($item) && $item !== ''));
        }

        return [];
    }
}
