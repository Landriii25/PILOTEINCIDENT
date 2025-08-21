{{-- resources/views/vendor/adminlte/partials/cwrapper/cwrapper-default.blade.php --}}

<div class="content-wrapper">

    {{-- Content Header (Page header) --}}
    @hasSection('content_header')
        <section class="content-header">
            <div class="{{ config('adminlte.classes_content_header', 'container-fluid') }}">
                @yield('content_header')
            </div>
        </section>
    @endif

    {{-- Main content --}}
    <section class="content">
        <div class="{{ config('adminlte.classes_content', 'container-fluid') }}">

            {{-- ✅ Flash global (s'affiche partout) --}}
            @include('partials.flash')

            {{-- Page Content --}}
            @yield('content')

        </div>
    </section>

</div>
