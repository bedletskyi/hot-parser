@extends('layouts.app')

@section('content')

    <div class="container">

        @component('components.breadcrumbs')
            @slot('title') Редактирование позиции @endslot
            @slot('parent') Главная @endslot
            @slot('active') Позиции @endslot
        @endcomponent

        <hr>

        <div class="col-md-8 col-md-offset-2">
            <form class="form-horizontal" action="{{ route('products.update', $product) }}" method="post">
                <input type="hidden" name="_method" value="put">
                {{ csrf_field() }}

                {{-- Form include --}}
                @include('products.partials.form')

            </form>
        </div>

    </div>


@endsection