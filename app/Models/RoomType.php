<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'max_people',
    ];

    protected function casts(): array
    {
        return [
            'base_price'  => 'decimal:2',
            'max_people'  => 'integer',
        ];
    }

    // ── Relations ─────────────────────────────────────────────────────────────

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenities', 'room_id', 'amenity_id');
    }
}