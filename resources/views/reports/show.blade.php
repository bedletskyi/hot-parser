@extends('layouts.app')

@section('content')

    <div class="container">
        <h2>Детальный отчет</h2>

        <div class="col-md-8 col-md-offset-2">
            @foreach($products as $key => $product)
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <p>
                            <a class="btn btn-link" href="{{ $product->Link }}" target="_blank">{{ $product->Name }}</a>
                            <span class="btn btn-success text-right">{{ $product->Price }}</span>
                            <span class="btn btn-warning text-right">{{ $product->Price - ($product->Price * 0.05) }}</span>
                        </p>
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed table-striped">
                            <thead>
                                <tr>
                                    <td>Магазин</td>
                                    <td>Цена</td>
                                    <td>Отклонение</td>
                                    <td>Дата обновления</td>
                                    <td>Ссылка</td>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prices[$key] as $price)
                                    <tr>
                                        <td>{{ $price->store }}</td>
                                        <td>{{ $price->price }}</td>
                                        <td>{{ substr((( $price->price / $product->Price) - 1) * 100, 0, 5) }}%</td>
                                        <td>{{ $price->date }}</td>
                                        <td>
                                            <a class="btn btn-primary btn-xs" href="{{ $price->link }}" target="_blank">В магазин</a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse()
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>


@endsection