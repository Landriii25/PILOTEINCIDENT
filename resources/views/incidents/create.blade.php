@extends('adminlte::page')

@section('title', 'Créer un incident')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Créer un incident</h1>
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list mr-1"></i> Tous les incidents
        </a>
    </div>
@endsection

@section('content')

    {{-- Résumé d’erreurs (si besoin) --}}
    @if ($errors->any())
        <div class="alert alert-danger" style="border-radius:12px">
            <div class="d-flex">
                <i class="fas fa-exclamation-circle fa-lg mr-2 mt-1"></i>
                <div>
                    <strong>Veuillez corriger les champs suivants :</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Bandeau code prévisionnel (affichage uniquement) --}}
    <div class="alert alert-info shadow-sm mb-4 d-flex align-items-center justify-content-between"
         style="border-radius:14px;">
        <div class="d-flex align-items-center">
            <i class="fas fa-hashtag fa-lg mr-2"></i>
            <div>
                <div style="font-weight:600; line-height:1">Code prévisionnel</div>
                <small class="text-white-50">Le code final sera généré automatiquement lors de l’enregistrement</small>
            </div>
        </div>
        <div class="badge badge-primary px-3 py-2" style="font-size:1rem;border-radius:10px;">
            {{ $previewCode ?? 'INC—' }}
        </div>
    </div>

    <div class="card shadow-sm" style="border-radius:14px; overflow:hidden;">
        <div class="card-body">
            {{-- MODIFICATION 1 : Ajout de enctype="multipart/form-data" pour permettre l'envoi de fichiers --}}
            <form action="{{ route('incidents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Ligne 1 : Titre + Priorité --}}
                <div class="form-row">
                    <div class="form-group col-md-7">
                        <label class="mb-1 font-weight-semibold">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" value="{{ old('titre') }}" class="form-control" required
                               placeholder="Décrivez brièvement le problème…">
                        @error('titre') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-5">
                        <label class="mb-1 font-weight-semibold">Priorité <span class="text-danger">*</span></label>
                        <select name="priorite" class="form-control" required>
                            <option value="">— Sélectionner —</option>
                            @foreach(\App\Models\Incident::PRIORITES as $p)
                                <option value="{{ $p }}" @selected(old('priorite')===$p)>{{ $p }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Critique = impact majeur · Haute/Moyenne/Basse selon l’urgence.
                        </small>
                        @error('priorite') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="form-group">
                    <label class="mb-1 font-weight-semibold">Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="4" class="form-control" required
                              placeholder="Détails, étapes pour reproduire, messages d’erreur, captures, etc.">{{ old('description') }}</textarea>
                    @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- MODIFICATION 2 : Ajout du champ pour les pièces jointes --}}
                <div class="form-group">
                    <label class="mb-1 font-weight-semibold">Pièces jointes (optionnel)</label>
                    <div class="custom-file">
                        <input type="file" name="attachments[]" id="attachments" class="custom-file-input" multiple>
                        <label class="custom-file-label" for="attachments" data-browse="Parcourir">Choisir des fichiers...</label>
                    </div>
                    <small class="form-text text-muted">
                        Vous pouvez joindre plusieurs fichiers (max 5). Formats : JPG, PDF, DOCX, etc.
                    </small>
                    @error('attachments.*') <small class="text-danger">{{ $message }}</small> @enderror
                </div>


                {{-- Ligne 2 : Application / Service (auto) / Technicien (filtré) --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="mb-1 font-weight-semibold">Application <span class="text-danger">*</span></label>
                        <select id="application_id" name="application_id" class="form-control" required>
                            <option value="">— Sélectionner —</option>
                            @foreach($apps as $app)
                                <option value="{{ $app->id }}" @selected(old('application_id')==$app->id)>
                                    {{ $app->nom }}
                                </option>
                            @endforeach
                        </select>
                        @error('application_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label class="mb-1 font-weight-semibold">Service (auto)</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-sitemap"></i></span>
                            </div>
                            <input id="service_name" type="text" class="form-control" value="{{ old('service_name') }}" readonly
                                   placeholder="Sélectionnez d’abord l’application">
                            <input id="service_id" name="service_id" type="hidden" value="{{ old('service_id') }}">
                        </div>
                        <small class="form-text text-muted">
                            Renseigné automatiquement en fonction de l’application choisie.
                        </small>
                    </div>

                    <div class="form-group col-md-4">
                        <label class="mb-1 font-weight-semibold">Technicien assigné</label>
                        <select id="technicien_id" name="technicien_id" class="form-control">
                            <option value="">— Aucun —</option>
                            @foreach($techs as $t)
                                <option value="{{ $t->id }}"
                                        data-service="{{ $t->service_id }}"
                                        @selected(old('technicien_id')==$t->id)>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Liste filtrée automatiquement selon le service détecté.
                        </small>
                        @error('technicien_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex justify-content-end mt-3">
                    <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-times mr-1"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('css')
<style>
    .font-weight-semibold { font-weight: 600; }
    .card .form-control { border-radius: 10px; }
    .input-group-text { border-radius: 10px 0 0 10px; }
    .btn { border-radius: 10px; }
</style>
@endpush

@section('js')
    {{-- On garde votre script existant pour les listes déroulantes dépendantes --}}
    <script>
        // La carte est maintenant correctement remplie par le contrôleur
        const APP_SERVICES = @json($mapAppServices ?? []);
        const appSelect = document.getElementById('application_id');
        const svcNameInput = document.getElementById('service_name');
        const svcIdInput = document.getElementById('service_id');
        const techSelect = document.getElementById('technicien_id');

        function refreshServiceAndTech() {
            const appId = appSelect.value;
            const service = APP_SERVICES[appId] || null;

            // Mettre à jour les champs du service
            if (service && service.id) {
                svcNameInput.value = service.nom || '';
                svcIdInput.value = service.id;
            } else {
                svcNameInput.value = '';
                svcIdInput.value = '';
            }

            // Filtrer la liste des techniciens
            const serviceId = service ? String(service.id) : null;
            const oldTechnicien = "{{ old('technicien_id') }}";
            let isOldTechnicienVisible = false;

            techSelect.querySelectorAll('option').forEach(option => {
                if (!option.value) return; // On garde toujours l'option "— Aucun —"
                const technicienServiceId = option.getAttribute('data-service');
                const shouldBeVisible = !serviceId || (technicienServiceId === serviceId);

                option.style.display = shouldBeVisible ? '' : 'none';

                if (!shouldBeVisible && option.selected) {
                    techSelect.value = '';
                }

                if (option.value === oldTechnicien && shouldBeVisible) {
                    isOldTechnicienVisible = true;
                }
            });

            if (isOldTechnicienVisible) {
                techSelect.value = oldTechnicien;
            }
        }

        appSelect.addEventListener('change', refreshServiceAndTech);
        document.addEventListener('DOMContentLoaded', refreshServiceAndTech);
    </script>

    {{-- MODIFICATION 3 (BONUS UX) : Script pour afficher le nom des fichiers sélectionnés --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('attachments');
            if (fileInput) {
                fileInput.addEventListener('change', function (e) {
                    const label = this.nextElementSibling;
                    const files = e.target.files;
                    if (files.length > 1) {
                        label.innerText = `${files.length} fichiers sélectionnés`;
                    } else if (files.length === 1) {
                        label.innerText = files[0].name;
                    } else {
                        label.innerText = 'Choisir des fichiers...';
                    }
                });
            }
        });
    </script>
@endsection
