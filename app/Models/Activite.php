<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Activite extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'titre',
        'slug',
        'description',
        'date_activite',
        'publie',
    ];

    protected $casts = [
        'date_activite' => 'date',
        'publie' => 'boolean',
    ];

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medias(): HasMany
    {
        return $this->hasMany(Media::class, 'model_id')
                    ->where('model_type', self::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('videos')
             ->acceptsMimeTypes(['video/mp4', 'video/avi']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(400)
             ->height(300)
             ->performOnCollections('photos');

        $this->addMediaConversion('medium')
             ->width(800)
             ->height(600)
             ->performOnCollections('photos');
    }

    public function scopePubliees($query)
    {
        return $query->where('publie', true);
    }

    // Génère un slug unique automatiquement
    public static function genererSlug(string $titre): string
    {
        $slug = \Str::slug($titre);
        $count = static::where('slug', 'like', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
