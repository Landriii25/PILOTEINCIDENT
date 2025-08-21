@extends('adminlte::page')
@section('title', 'Éditer un service')

@section('content_header')
    <h1 class="m-0">Éditer le service</h1>
@endsection

@section('content')
    <x-errors.list />

    <div class="card">
        <form action="{{ route('services.update', $service) }}" method="POST">
            @csrf
            @method('PUT')
            @include('services.partials._form', [
                'service' => $service,
                'chefs'   => $chefs ?? collect(),
                'submit'  => 'Mettre à jour'
            ])
        </form>
    </div>
@endsection
