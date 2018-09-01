@extends('layouts.app')

@section('content')

    <div class="container">

        @component('components.breadcrumbs')
            @slot('title') Импорт позиций @endslot
            @slot('parent') Главная @endslot
            @slot('active') Импорт @endslot
        @endcomponent

        <hr>

        <div class="col-md-8 col-md-offset-2">

            @if($errors->first('file'))
                <div class="alert alert-danger" role="alert">
                    {{ $errors->first('file') }}
                </div>
            @endif

            <form class="form-horizontal" action="{{ route('import') }}" method="post" enctype="multipart/form-data" >
                {{ csrf_field() }}

                <label for="file">Выберите файл Excel:</label>
                <input type="file" id="file" name="file" required>
                <br>
                <input type="checkbox" name="reset_all" value="true" id="reset">
                <label for="reset">Удалить все позиции перед импортом</label>
                <hr>
                <input type="submit" class="btn btn-primary" value="Загрузить">


            </form>
        </div>

    </div>


@endsection