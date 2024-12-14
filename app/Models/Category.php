<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'desc',
        'code',
        'category_photo',
        'category_type',

    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
