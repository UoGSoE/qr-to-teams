<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'shortcode',
        'is_default',
        'called_count',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_form' => 'boolean',
        ];
    }

    public static function createNew(string $url, string $name, bool $isDefault)
    {
        if ($isDefault) {
            static::where('is_default', true)->update(['is_default' => false]);
        }

        $webhook = static::create([
            'name' => $name,
            'url' => $url,
            'is_default' => $isDefault,
            'shortcode' => Str::random(64),
        ]);
        $webhook->update([
            'shortcode' => static::generateShortcode($webhook->id),
        ]);

        return $webhook;
    }

    public static function generateShortcode($id)
    {
        return \Vinkla\Hashids\Facades\Hashids::encode(intval($id));
    }

    public function registerCalled()
    {
        $this->update([
            'updated_at' => now(),
            'called_count' => $this->called_count + 1,
        ]);
    }
}
