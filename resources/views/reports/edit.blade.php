@extends('adminlte::page')
@section('title','Éditer le rapport')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Éditer le rapport — {{ $report->ref }}</h1>
        <a href="{{ route('reports.show',$report) }}" class="btn btn-light">
            <i class="fas fa-eye mr-1"></i> Voir
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('reports.update', $report) }}" method="POST">
                @include('reports._form', ['report' => $report])
            </form>
        </div>
    </div>
@endsection
