<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'overtime_date',
        'work_type',
        'schedule_type',
        'start_time',
        'end_time',
        'notes',
        'latitude',
        'longitude',
        'location_valid',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'location_valid' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function durationMinutes(): int
    {
        $start = Carbon::parse($this->overtime_date->format('Y-m-d') . ' ' . $this->start_time);
        $end = Carbon::parse($this->overtime_date->format('Y-m-d') . ' ' . $this->end_time);

        return max(0, $start->diffInMinutes($end));
    }

    public function durationLabel(): string
    {
        $minutes = $this->durationMinutes();
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return $hours . ' jam ' . $mins . ' menit';
        }
        if ($hours > 0) {
            return $hours . ' jam';
        }

        return $mins . ' menit';
    }

    public function scheduleTypeLabel(): string
    {
        return match ($this->schedule_type) {
            'piket' => 'Piket / Terjadwal',
            'additional' => 'Tidak Terjadwal / Additional',
            default => 'Tidak Terjadwal / Additional',
        };
    }
}
