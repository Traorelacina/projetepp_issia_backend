<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : media  (Spatie Media Library v10/v11)
 *
 * Requise par :
 *  - ActiviteController (Admin) : addMedia()->toMediaCollection('photos')
 *                                 clearMediaCollection('photos'), clearMediaCollection('videos')
 *  - ActiviteController (Public) : getFirstMediaUrl(), getMedia('photos'), getUrl(), getUrl('thumb')
 *  - MediaController : Media::where('collection_name','photos'), $media->getUrl(), getUrl('thumb')
 *  - DashboardController : Media::where('collection_name','photos')->count()
 *
 * Collections utilisées : 'photos', 'videos'
 * Conversions utilisées : 'thumb', 'medium'
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            // Relation polymorphique vers le modèle propriétaire (ex: Activite)
            $table->morphs('model');

            // Identifiant UUID Spatie
            $table->uuid('uuid')->nullable()->unique();

            // Collection (ex: 'photos', 'videos')
            $table->string('collection_name');

            // Nom lisible du fichier
            $table->string('name');

            // Nom du fichier sur disque
            $table->string('file_name');

            // MIME type (ex: image/jpeg, video/mp4)
            $table->string('mime_type')->nullable();

            // Disque de stockage (ex: public, s3)
            $table->string('disk');

            // Disque pour les conversions
            $table->string('conversions_disk')->nullable();

            // Taille en octets
            $table->unsignedBigInteger('size');

            // Manipulations JSON
            $table->json('manipulations');

            // Propriétés personnalisées JSON (dimensions, etc.)
            $table->json('custom_properties');

            // Conversions générées JSON (thumb, medium…)
            $table->json('generated_conversions');

            // Informations responsives JSON
            $table->json('responsive_images');

            // Ordre dans la collection
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->nullableTimestamps();

            // Note: index on (model_type, model_id) is already created by morphs('model') above
            $table->index('collection_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};