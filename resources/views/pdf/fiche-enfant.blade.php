<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Fiche d'inscription — {{ $insc->nom_enfant }} {{ $insc->prenoms_enfant }}</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 10.5px;
      line-height: 1.5;
      color: #1a2e1e;
      background: #fff;
      width: 100%;
    }

    /* ── PAGE : marges réduites pour avoir plus d'espace utile ── */
    .page {
      width: 190mm;       /* largeur utile A4 = 210 - marges */
      margin: 0 auto;
      padding: 6mm 0;
      background: #fff;
    }

    /* ══════════════ EN-TÊTE ══════════════ */
    .header {
      background: #0f4a25;
      border-radius: 7px;
      padding: 8px 12px;
      margin-bottom: 6px;
    }
    .ht { width: 100%; border-collapse: collapse; }
    .ht td { vertical-align: middle; padding: 0; }
    .ht .c-logo  { width: 54px; }
    .ht .c-info  { padding-left: 9px; }
    .ht .c-badge { width: 95px; text-align: right; }

    .logo-img {
      width: 44px; height: 44px;
      border-radius: 50%;
      border: 2px solid #F5A623;
      object-fit: cover;
      display: block;
    }
    .logo-fallback {
      width: 44px; height: 44px;
      background: #F5A623;
      border-radius: 50%;
      display: inline-block;
      text-align: center;
      line-height: 44px;
      font-size: 18px;
      font-weight: 900;
      color: #0f4a25;
    }
    .h-title { font-size: 13px; font-weight: 700; color: #fff; }
    .h-sub   { font-size: 8px; color: rgba(255,255,255,0.55); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.8px; }
    .h-badge {
      background: #1a6b35;
      border: 1px solid #F5A623;
      border-radius: 20px;
      padding: 2px 7px;
      font-size: 8.5px;
      font-weight: 700;
      color: #F5A623;
      display: inline-block;
    }
    .h-label { color: rgba(255,255,255,0.38); font-size: 7.5px; margin-top: 3px; }

    /* ══════════════ BANDE ENFANT ══════════════ */
    .band {
      background: #f4f8f5;
      border: 1px solid #dae8df;
      border-radius: 6px;
      padding: 6px 10px;
      margin-bottom: 6px;
    }
    .bt { width: 100%; border-collapse: collapse; }
    .bt td { vertical-align: middle; padding: 0; }

    .avatar {
      width: 34px; height: 34px;
      background: #1B7A3E;
      border-radius: 50%;
      display: inline-block;
      text-align: center;
      line-height: 34px;
      font-size: 14px;
      font-weight: 900;
      color: #fff;
    }
    .e-nom  { font-size: 13px; font-weight: 700; color: #0c1a10; }
    .e-meta { font-size: 8.5px; color: #6b7c70; margin-top: 1px; }

    .b-section {
      display: inline-block;
      background: #1B7A3E;
      color: #fff;
      font-size: 8px; font-weight: 700;
      padding: 2px 7px;
      border-radius: 20px;
      text-transform: uppercase;
    }
    .b-statut {
      display: inline-block;
      font-size: 8px; font-weight: 700;
      padding: 2px 7px;
      border-radius: 20px;
    }
    .sv { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .sa { background: #fef9c3; color: #92400e; border: 1px solid #fde68a; }
    .sr { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    /* ══════════════ LAYOUT PRINCIPAL ══════════════
       SOLUTION DomPDF : 2 tables séparées côte à côte
       via une table mère à 2 colonnes avec valign=top.
       On NE met PAS de contenu à l'intérieur d'une seule grande <td> —
       chaque colonne est autonome, rien ne "pousse" l'autre.
    ══════════════════════════════════════════════ */
    .layout  { width: 100%; border-collapse: collapse; }
    .layout td { vertical-align: top; padding: 0; }
    .l-main  { width: 61%; padding-right: 5px; }
    .l-side  { width: 39%; padding-left: 5px; }

    /* ══════════════ CARTES ══════════════ */
    .card {
      border: 1px solid #dae8df;
      border-radius: 7px;
      margin-bottom: 5px;
      /* page-break-inside: avoid provoque parfois des bugs DomPDF → retiré */
    }
    .c-head {
      background: #f4f8f5;
      border-bottom: 1px solid #dae8df;
      padding: 4px 9px;
    }
    .c-title { font-size: 9px; font-weight: 700; color: #1B7A3E; text-transform: uppercase; letter-spacing: 1.2px; }
    .c-acc   { display: inline-block; width: 3px; height: 9px; background: #F5A623; border-radius: 2px; margin-right: 4px; vertical-align: middle; }
    .c-body  { padding: 5px 9px; }

    /* ══════════════ TABLEAU INFOS ══════════════ */
    .it { width: 100%; border-collapse: collapse; }
    .it td {
      padding: 2.5px 0;
      border-bottom: 1px solid #f0f6f2;
      vertical-align: top;
      /* word-wrap pour éviter tout débordement horizontal */
      word-wrap: break-word;
      overflow-wrap: break-word;
    }
    .it tr:last-child td { border-bottom: none; }
    .il { font-size: 9.5px; color: #6b7c70; width: 42%; padding-right: 5px; }
    .iv { font-size: 10px; font-weight: 600; color: #0c1a10; }

    /* ══════════════ PARENTS ══════════════ */
    .pl {
      display: block;
      font-size: 8px; font-weight: 700;
      color: #F5A623;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      padding: 3px 0 2px;
      border-bottom: 1px solid #fde9c0;
      margin-bottom: 2px;
      margin-top: 4px;
    }
    .pl-t { color: #6b7c70; border-bottom-color: #dae8df; }
    .pl-first { margin-top: 0; }

    /* ══════════════ PAIEMENT ══════════════ */
    .pay-box {
      background: #f9fbf9;
      border: 1px solid #dae8df;
      border-radius: 6px;
      padding: 6px 8px;
      margin-bottom: 4px;
    }
    .pr { width: 100%; border-collapse: collapse; }
    .pr td { padding: 1.5px 0; font-size: 9.5px; vertical-align: middle; }
    .p-lbl  { color: #6b7c70; }
    .p-tot  { font-weight: 700; color: #0c1a10; text-align: right; }
    .p-vrs  { font-weight: 700; color: #15803d; text-align: right; }
    .p-rst  { font-weight: 700; color: #dc2626; text-align: right; }
    .sep    { border: none; border-top: 1px solid #dae8df; margin: 3px 0; }

    .prg-wrap { background: #e5f0e8; border-radius: 20px; height: 5px; width: 100%; overflow: hidden; margin-top: 4px; }
    .prg-bar  { height: 5px; border-radius: 20px; background: #1B7A3E; }
    .prg-lbl  { font-size: 8px; color: #6b7c70; text-align: right; margin-top: 1px; }

    /* ══════════════ CHIPS ══════════════ */
    .chip-o { display: inline-block; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 1px 5px; border-radius: 20px; font-size: 8px; font-weight: 700; }
    .chip-n { display: inline-block; background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; padding: 1px 5px; border-radius: 20px; font-size: 8px; font-weight: 700; }

    /* ══════════════ OBSERVATIONS ══════════════ */
    .obs {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 6px;
      padding: 5px 8px;
      margin-top: 4px;
      font-size: 9.5px;
      color: #78350f;
      line-height: 1.55;
    }
    .obs-t { font-size: 8px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px; }

    /* ══════════════ SIGNATURES ══════════════ */
    .sig-box { border: 1px dashed #dae8df; border-radius: 5px; min-height: 35px; }
    .sig-lbl { font-size: 7.5px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.8px; text-align: center; margin-top: 3px; }

    /* ══════════════ MÉTADONNÉES ══════════════ */
    .meta { background: #f9fbf9; border: 1px solid #dae8df; border-radius: 6px; padding: 5px 8px; }
    .meta-t { font-size: 8px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1.2px; margin-bottom: 3px; }

    /* ══════════════ PIED DE PAGE ══════════════ */
    .footer {
      margin-top: 6px;
      padding-top: 5px;
      border-top: 1px solid #dae8df;
    }
    .ft { width: 100%; border-collapse: collapse; }
    .ft td { vertical-align: top; padding: 0; font-size: 8px; color: #9ca3af; }

    /* ══════════════ ECHEANCIER ══════════════ */
    .ech { font-size: 8px; color: #6b7c70; margin-top: 4px; line-height: 1.8; }
    .ech strong { color: #0c1a10; }
  </style>
</head>
<body>
<div class="page">

  {{-- ══════════════ EN-TÊTE ══════════════ --}}
  <div class="header">
    <table class="ht">
      <tbody><tr>
        <td class="c-logo">
          @if(!empty($logo_base64))
            <img src="{{ $logo_base64 }}" alt="Logo CPPE" class="logo-img" />
          @else
            <span class="logo-fallback">C</span>
          @endif
        </td>
        <td class="c-info">
          <div class="h-title">Centre de Protection de la Petite Enfance</div>
          <div class="h-sub">Complexe Socio-Éducatif d'Issia · Haut-Sassandra · Côte d'Ivoire</div>
          <div class="h-sub">Ministère de la Femme, de la Famille et de l'Enfant</div>
        </td>
        <td class="c-badge">
          <div class="h-badge">{{ $insc->annee_scolaire }}</div>
          <div class="h-label">Fiche d'inscription</div>
        </td>
      </tr></tbody>
    </table>
  </div>

  {{-- ══════════════ VARIABLES PARTAGÉES ══════════════ --}}
  @php
    $sC = match($insc->statut ?? 'en_attente') { 'valide'=>'sv','refuse'=>'sr',default=>'sa' };
    $sL = match($insc->statut ?? 'en_attente') { 'valide'=>'Validé','refuse'=>'Refusé',default=>'En attente' };
    $pC = match($insc->statut_paiement ?? 'non_paye') { 'complet'=>'sv','partiel'=>'sa',default=>'sr' };
    $pL = match($insc->statut_paiement ?? 'non_paye') { 'complet'=>'Complet','partiel'=>'Partiel',default=>'Non payé' };
    $total  = 50000;
    $verse  = (int)($insc->montant_verse ?? 0);
    $reste  = $total - $verse;
    $pct    = $total > 0 ? min(100, round($verse / $total * 100)) : 0;
    $annee1 = explode('-', $insc->annee_scolaire ?? '2025-2026')[0];
  @endphp

  {{-- ══════════════ BANDE ENFANT ══════════════ --}}
  <div class="band">
    <table class="bt">
      <tbody><tr>
        <td style="width:42px;">
          <span class="avatar">{{ mb_strtoupper(mb_substr($insc->nom_enfant,0,1)) }}</span>
        </td>
        <td style="padding-left:9px;">
          <div class="e-nom">{{ $insc->nom_enfant }} {{ $insc->prenoms_enfant }}</div>
          <div class="e-meta">
            {{ $insc->sexe==='M' ? 'Garçon' : 'Fille' }} &nbsp;·&nbsp;
            Né(e) le {{ \Carbon\Carbon::parse($insc->date_naissance)->locale('fr')->isoFormat('D MMMM YYYY') }}
            @if($insc->lieu_naissance) &nbsp;à&nbsp;{{ $insc->lieu_naissance }} @endif
          </div>
        </td>
        <td style="text-align:right; width:155px;">
          <span class="b-section">{{ strtoupper($insc->section) }}</span>
          &nbsp;<span class="b-statut {{ $sC }}">{{ $sL }}</span>
          <div style="margin-top:3px; font-size:8px; color:#9ca3af;">Dossier #{{ $insc->id }}</div>
        </td>
      </tr></tbody>
    </table>
  </div>

  {{-- ══════════════════════════════════════════════════════
       LAYOUT PRINCIPAL : table mère 2 colonnes
       Chaque colonne est AUTONOME — DomPDF gère chacune
       indépendamment, pas de débordement croisé.
  ══════════════════════════════════════════════════════ --}}
  <table class="layout">
    <tbody><tr>

      {{-- ────────── COLONNE GAUCHE (61%) ────────── --}}
      <td class="l-main">

        {{-- CARTE ENFANT --}}
        <div class="card">
          <div class="c-head"><span class="c-acc"></span><span class="c-title">Informations de l'enfant</span></div>
          <div class="c-body">
            <table class="it"><tbody>
              <tr><td class="il">Nom &amp; prénoms</td><td class="iv">{{ $insc->nom_enfant }} {{ $insc->prenoms_enfant }}</td></tr>
              <tr><td class="il">Date de naissance</td><td class="iv">{{ \Carbon\Carbon::parse($insc->date_naissance)->locale('fr')->isoFormat('D MMMM YYYY') }}</td></tr>
              <tr><td class="il">Lieu de naissance</td><td class="iv">{{ $insc->lieu_naissance ?? '—' }}</td></tr>
              <tr><td class="il">Sexe</td><td class="iv">{{ $insc->sexe==='M' ? 'Masculin' : 'Féminin' }}</td></tr>
              <tr><td class="il">Nationalité</td><td class="iv">{{ $insc->nationalite ?? '—' }}</td></tr>
              <tr><td class="il">Section</td><td class="iv">{{ strtoupper($insc->section) }}</td></tr>
              <tr><td class="il">Année scolaire</td><td class="iv">{{ $insc->annee_scolaire }}</td></tr>
              <tr>
                <td class="il">Cantine</td>
                <td class="iv">
                  @if($insc->cantine)<span class="chip-o">Oui</span>@else<span class="chip-n">Non</span>@endif
                </td>
              </tr>
              @if($insc->ancienne_ecole)
              <tr><td class="il">Ancienne école</td><td class="iv">{{ $insc->ancienne_ecole }}</td></tr>
              @endif
            </tbody></table>
          </div>
        </div>

        {{-- CARTE PARENTS --}}
        <div class="card">
          <div class="c-head"><span class="c-acc"></span><span class="c-title">Parents / Tuteur légal</span></div>
          <div class="c-body">

            @if($insc->nom_pere)
              <span class="pl pl-first">Père</span>
              <table class="it"><tbody>
                <tr><td class="il">Nom complet</td><td class="iv">{{ $insc->nom_pere }}</td></tr>
                @if($insc->profession_pere)<tr><td class="il">Profession</td><td class="iv">{{ $insc->profession_pere }}</td></tr>@endif
                @if($insc->telephone_pere)<tr><td class="il">Téléphone</td><td class="iv">{{ $insc->telephone_pere }}</td></tr>@endif
              </tbody></table>
            @endif

            @if($insc->nom_mere)
              <span class="pl">Mère</span>
              <table class="it"><tbody>
                <tr><td class="il">Nom complet</td><td class="iv">{{ $insc->nom_mere }}</td></tr>
                @if($insc->profession_mere)<tr><td class="il">Profession</td><td class="iv">{{ $insc->profession_mere }}</td></tr>@endif
                @if($insc->telephone_mere)<tr><td class="il">Téléphone</td><td class="iv">{{ $insc->telephone_mere }}</td></tr>@endif
              </tbody></table>
            @endif

            @if($insc->nom_tuteur)
              <span class="pl pl-t">Tuteur légal</span>
              <table class="it"><tbody>
                <tr><td class="il">Nom complet</td><td class="iv">{{ $insc->nom_tuteur }}</td></tr>
                @if($insc->telephone_tuteur)<tr><td class="il">Téléphone</td><td class="iv">{{ $insc->telephone_tuteur }}</td></tr>@endif
              </tbody></table>
            @endif

            @if($insc->adresse_domicile)
              <table class="it" style="margin-top:3px;"><tbody>
                <tr><td class="il">Adresse domicile</td><td class="iv">{{ $insc->adresse_domicile }}</td></tr>
              </tbody></table>
            @endif

          </div>
        </div>

        {{-- OBSERVATIONS --}}
        @if($insc->observations)
        <div class="obs">
          <div class="obs-t">Observations</div>
          {{ $insc->observations }}
        </div>
        @endif

      </td>{{-- /col-main --}}

      {{-- ────────── COLONNE DROITE (39%) ────────── --}}
      <td class="l-side">

        {{-- STATUT DU DOSSIER --}}
        <div class="card">
          <div class="c-head"><span class="c-acc"></span><span class="c-title">Statut du dossier</span></div>
          <div class="c-body">
            <table class="it"><tbody>
              <tr>
                <td class="il">Dossier</td>
                <td class="iv"><span class="b-statut {{ $sC }}">{{ $sL }}</span></td>
              </tr>
              <tr>
                <td class="il">Paiement</td>
                <td class="iv"><span class="b-statut {{ $pC }}">{{ $pL }}</span></td>
              </tr>
            </tbody></table>
          </div>
        </div>

        {{-- DÉTAIL PAIEMENT --}}
        <div class="card">
          <div class="c-head"><span class="c-acc"></span><span class="c-title">Détail paiement</span></div>
          <div class="c-body">
            <div class="pay-box">
              <table class="pr"><tbody>
                <tr><td class="p-lbl">Scolarité totale</td><td class="p-tot">{{ number_format($total,0,',',' ') }} FCFA</td></tr>
              </tbody></table>
              <hr class="sep" />
              <table class="pr"><tbody>
                <tr><td class="p-lbl">Montant versé</td><td class="p-vrs">{{ number_format($verse,0,',',' ') }} FCFA</td></tr>
                <tr><td class="p-lbl">Reste à payer</td><td class="p-rst">{{ number_format($reste,0,',',' ') }} FCFA</td></tr>
              </tbody></table>
              <div class="prg-wrap"><div class="prg-bar" style="width:{{ $pct }}%;"></div></div>
              <div class="prg-lbl">{{ $pct }}% payé</div>
            </div>
            <div class="ech">
              <strong style="display:block; margin-bottom:2px;">Échéancier :</strong>
              1er versement : <strong>31 000 FCFA</strong> — à l'inscription<br>
              2ème versement : <strong>15 000 FCFA</strong> — nov. {{ $annee1 }}<br>
              3ème versement : <strong>10 000 FCFA</strong> — déc. {{ $annee1 }}
            </div>
          </div>
        </div>

        {{-- SIGNATURES --}}
        <div class="card">
          <div class="c-head"><span class="c-acc"></span><span class="c-title">Signatures</span></div>
          <div class="c-body">
            <table style="width:100%; border-collapse:collapse;"><tbody><tr>
              <td style="width:50%; padding-right:4px; vertical-align:top;">
                <div class="sig-box"></div>
                <div class="sig-lbl">Parent / Tuteur</div>
              </td>
              <td style="width:50%; padding-left:4px; vertical-align:top;">
                <div class="sig-box"></div>
                <div class="sig-lbl">Direction CPPE</div>
              </td>
            </tr></tbody></table>
          </div>
        </div>

        {{-- MÉTADONNÉES --}}
        <div class="meta">
          <div class="meta-t">Métadonnées</div>
          <table class="it"><tbody>
            <tr><td class="il">ID dossier</td><td class="iv">#{{ $insc->id }}</td></tr>
            <tr>
              <td class="il">Soumis le</td>
              <td class="iv">{{ \Carbon\Carbon::parse($insc->created_at)->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}</td>
            </tr>
            @if($insc->updated_at && $insc->updated_at != $insc->created_at)
            <tr>
              <td class="il">Modifié le</td>
              <td class="iv">{{ \Carbon\Carbon::parse($insc->updated_at)->locale('fr')->isoFormat('D MMM YYYY') }}</td>
            </tr>
            @endif
            <tr><td class="il">Imprimé le</td><td class="iv">{{ $date_impression }}</td></tr>
          </tbody></table>
        </div>

      </td>{{-- /col-side --}}

    </tr></tbody>
  </table>{{-- /layout --}}

  {{-- ══════════════ PIED DE PAGE ══════════════ --}}
  <div class="footer">
    <table class="ft"><tbody><tr>
      <td style="text-align:left;">
        <strong style="color:#1B7A3E;">CPPE d'Issia</strong><br>
        Tél : 07 07 18 65 59 / 05 06 48 22 01
      </td>
      <td style="text-align:center;">
        Document généré le {{ $date_impression }}
      </td>
      <td style="text-align:right;">
        Complexe Socio-Éducatif d'Issia<br>
        Haut-Sassandra, Côte d'Ivoire
      </td>
    </tr></tbody></table>
  </div>

</div>{{-- /.page --}}
</body>
</html>