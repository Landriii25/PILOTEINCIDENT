@extends('adminlte::page')
@section('title', 'Nouveau service')

@section('content_header')
    <h1 class="m-0">Créer un service</h1>
@endsection

@section('content')
    <x-errors.list />

    <div class="card">
        <form action="{{ route('services.store') }}" method="POST">
            @csrf
            @include('services.partials._form', [
                'service' => null,
                'chefs'   => $chefs ?? collect(),
                'submit'  => 'Enregistrer'
            ])
        </form>
    </div>
@endsection
