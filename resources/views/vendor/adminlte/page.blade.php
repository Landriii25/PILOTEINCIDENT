@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    {{-- CSS global custom --}}
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('css')
    @yield('css')
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if(!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer (toujours visible) --}}
        @include('adminlte::partials.footer.footer')

        {{-- Right Control Sidebar --}}
        @if($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
@stop

@section('adminlte_js')
    {{-- Auto-wrapper : met toutes les <table> dans .table-responsive si besoin --}}
    <script>
      (function () {
        document.addEventListener('DOMContentLoaded', function () {
          document.querySelectorAll('.content-wrapper table').forEach(function (tbl) {
            // déjà dans un wrapper ?
            if (tbl.closest('.table-responsive, .table-wrap')) return;

            // wrap
            const wrap = document.createElement('div');
            wrap.className = 'table-responsive';
            tbl.parentNode.insertBefore(wrap, tbl);
            wrap.appendChild(tbl);
          });
        });
      })();
    </script>
    @stack('js')
    @yield('js')
@stop
