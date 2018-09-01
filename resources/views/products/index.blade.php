@extends('layouts.app')

@section('content')

    <div class="container">

        @component('components.breadcrumbs')
            @slot('title') Список позиций @endslot
            @slot('parent') Главная @endslot
            @slot('active') Позиции @endslot
        @endcomponent

        <hr>

        @if(session('message'))
            <div class="alert alert-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        <a href="{{ route('products.create') }}" class="btn btn-primary pull-right"><i class="fas fa-plus"></i> Добавить позицию</a>
        <a href="{{ route('page-import') }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Импорт</a>

        <table class="table table-striped">
            <thead>
                <th class="text-left">SKU</th>
                <th class="text-center">Наименование</th>
                <th class="text-center">РРЦ</th>
                <th class="text-center">Норма</th>
                <th class="text-right">Действие</th>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->SKU }}</td>
                        <td>{{ $product->Name }}</td>
                        <td class="text-center">{{ $product->Price }}</td>
                        <td class="text-center">{{ $product->Price - ($product->Price * 0.05) }}</td>
                        <td class="text-right">
                            <div class="btn-group" role="group">
                                <form onsubmit="if (confirm('Удалить выбранную позицию?')){return true} else {return false}" action="{{ route('products.destroy', $product) }}" method="post">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    <a class="btn btn-primary" href="{{ route('products.edit', $product) }}">
                                        <i class="far fa-edit"></i>
                                    </a>
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <h2>Нет добавленых позиций</h2>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <ul class="pagination pull-right">
                            {{ $products->links() }}
                        </ul>
                    </td>
                </tr>
            </tfoot>
        </table>

    </div>

@endsection