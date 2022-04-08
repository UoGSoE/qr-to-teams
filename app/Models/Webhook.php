<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'shortcode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function generateShortcode($id)
    {
        return \Vinkla\Hashids\Facades\Hashids::encode($id);
    }
}
