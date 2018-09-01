@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div id="parser" style="display: none;"  class="alert alert-info" role="alert">
                <p class="parser" style="font-size: 15px;">
                    Парсинг и обработка данных с Hotline <i style="vertical-align: middle" class="fas fa-cog fa-2x"></i>
                </p>
            </div>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

        </div>
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Панель</div>

                <div class="panel-body">
                    {{ date('H:i') }}

                    <a href="#" id="start" class="btn btn-success">Старт <i class="far fa-play-circle"></i></a>

                    <hr>

                    <table class="table table-striped">
                        <thead>
                        <th class="text-left">№</th>
                        <th class="text-center">Название</th>
                        <th class="text-right">Действие</th>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $report->name }}</td>
                                <td class="text-right">
                                    <div class="btn-group" role="group">
                                        <form onsubmit="if (confirm('Удалить выбранный отчет?')){return true} else {return false}" action="{{ route('reports.destroy', $report->id) }}" method="post">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            <a class="btn btn-primary" href="{{ route('reports.show', $report->id) }}">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('download', $report->id) }}" class="btn btn-success">
                                                <i class="fas fa-file-excel"></i>
                                            </a>
                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php $i++; ?>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <h2>Нет созданных отчетов</h2>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection