<?php

namespace App\Http\Controllers;

use App\Price;
use App\Product;
use Illuminate\Http\Request;
use App\Report;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function show(Report $report)
    {
        $products = Product::with('price')->get();

        foreach ($products as $key => $product) {
            $price[$key] = $product->price()
                    ->where('report_id', $report->id)
                    ->orderBy('price', 'asc')
                    ->get();
        }

        return view('reports.show')->with([
            'products' => $products,
            'prices' => $price
        ]);
    }

    public function destroy(Report $report)
    {
        DB::table('prices')->where('report_id', $report->id)->delete();

        $report->delete();

        return redirect()->route('home')->with([
            'status' => 'Отчет успешно удален'
        ]);
    }
}
