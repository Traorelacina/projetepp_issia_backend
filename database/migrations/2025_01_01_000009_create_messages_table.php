<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Table : messages
 *
 * Champs déduits de :
 *  - Public\ContactController   : validate() store
 *  - Admin\MessageController    : nonArchives(), nonLus(), marquerLu(), destroy() => archive=true
 *  - DashboardController        : Message::nonLus()->count(), nonArchives()->count()
 *
 * Particularités :
 *  - Pas de softDeletes Laravel : la suppression logique est gérée via archive=true
 *    (MessageController::destroy() fait update(['archive' => true]))
 *  - marquerLu() toggle lu = !lu
 *  - Scope nonArchives() : where('archive', false)
 *  - Scope nonLus()      : where('lu', false)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // ── Expéditeur ────────────────────────────────────────────
            $table->string('nom', 100);
            $table->string('email', 150);
            $table->string('telephone', 20)->nullable();

            // ── Contenu ───────────────────────────────────────────────
            $table->string('sujet', 200);
            $table->text('message');

            // ── Lecture ───────────────────────────────────────────────
            // marquerLu() toggle : lu = !lu
            $table->boolean('lu')->default(false);

            // ── Suppression logique ───────────────────────────────────
            // MessageController::destroy() => update(['archive' => true])
            // Scope nonArchives() => where('archive', false)
            $table->boolean('archive')->default(false);

            $table->timestamps();

            $table->index('lu');
            $table->index('archive');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
