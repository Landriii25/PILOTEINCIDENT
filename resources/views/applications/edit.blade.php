{{-- resources/views/applications/edit.blade.php --}}
@extends('adminlte::page')

@section('title','Éditer une application')

@section('content_header')
    <h1>Éditer l’application</h1>
@endsection

@section('content')
    <x-errors.list />

    <div class="card">
        <form method="POST" action="{{ route('applications.update', $application) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-group">
                    <label>Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" value="{{ old('nom', $application->nom) }}" required>
                </div>

                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" class="form-control">
                        <option value="">— Aucun —</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" @selected(old('service_id',$application->service_id)==$s->id)>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-control" required>
                        @foreach(['Actif','En maintenance','Retirée'] as $st)
                            <option value="{{ $st }}" @selected(old('statut',$application->statut)==$st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $application->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Logo (JPG/PNG, max 2 Mo)</label>
                    <input type="file" name="logo" class="form-control-file" accept="image/*" id="logo-input">
                    <small class="form-text text-muted">Une miniature 256×256 sera régénérée si vous changez le logo.</small>

                    <div class="mt-2 d-flex align-items-center">
                        @if($application->thumb_url)
                            <img src="{{ asset('storage/'.$application->thumb_url) }}" class="rounded shadow-sm mr-3" style="width:72px;height:72px;object-fit:cover" alt="thumb">
                        @else
                            <span class="text-muted mr-3">Aucune miniature</span>
                        @endif
                        <div id="preview" style="display:none;">
                            <img class="rounded shadow-sm" style="width:72px;height:72px;object-fit:cover" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('applications.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Annuler
                </a>
                <button class="btn btn-success">
                    <i class="fas fa-check mr-1"></i> Mettre à jour
                </button>
            </div>
        </form>
    </div>
@endsection

@push('js')
<script>
document.getElementById('logo-input')?.addEventListener('change', function(e){
    const img = document.querySelector('#preview img');
    const wrap = document.getElementById('preview');
    if(this.files && this.files[0]){
        img.src = URL.createObjectURL(this.files[0]);
        wrap.style.display = 'block';
    }else{
        wrap.style.display = 'none';
    }
});
</script>
@endpush
