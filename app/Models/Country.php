<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $fillable = [
        'country_name',
        'country_photo',
        'city_name',
        'event_name',
        'date',
        'desc_event',
        'event_photo',
        'latitude',
        'longitude',
        'country_desc'
    ];
}
