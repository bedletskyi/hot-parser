<?php

namespace App\Http\Controllers;

use App\Modules\Parser;
use App\Price;
use App\Product;
use App\Report;
use Illuminate\Http\Request;
use PHPExcel;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Font;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Writer_Excel5;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function getData()
    {
        return Report::orderBy('id', 'desc')->get();
    }

    public function show($id)
    {
        $products = Product::with('price')->get();

        foreach ($products as $key => $product) {
            $price[$key] = $product->price()
                ->where('report_id', $id)
                ->orderBy('price', 'asc')
                ->get();
        }

        return view('reports.show')->with([
            'products' => $products,
            'prices' => $price
        ]);
    }

    public function destroy($id)
    {
        Price::where('report_id', $id)->delete();

        $report = Report::findOrFail($id);
        $report->delete();
        return;
    }

    public function startParser(Request $request)
    {
        if ($request->message != 'start') {
            return false;
        }
        if ($request->get('message') == 'start') {

            $products = Product::all();
            $links = array();
            foreach ($products as $product) {
                $uri_parts = explode('/', $product->Link);
                $links[$product->id] = [
                    'url' => 'https://hotline.ua/' . $uri_parts[3] . '/' . $uri_parts[4] . '/'
                ];
            }
            $report_id = $this->createReport();
            $parser = new Parser($links, $report_id);
            $prices = $parser->getPrice();
            foreach ($prices as $price) {
                Price::create($price);
            }
            return 'ok';
        }
    }

    /**
     * Create a new report in the database
     *
     * @return mixed
     */
    public function createReport()
    {
        $model = Report::create([
            'name' => 'Hotline ' . date('d.m.y H:i')
        ]);
        return $model->id;
    }

    public function makeReport($id)
    {
        $xls = new PHPExcel();

        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle('Скан лист');

        $sheet->setCellValue("A1", 'SKU');
        $sheet->setCellValue("B1", 'Наименование');
        $sheet->setCellValue("C1", 'РРЦ');
        $sheet->setCellValue("D1", 'Норма');
        $sheet->setCellValue("E1", 'Название');
        $sheet->setCellValue("F1", 'Hotline');
        $sheet->setCellValue("G1", 'Отклонение');
        $sheet->setCellValue("H1", 'Дана скана');
        $sheet->setCellValue("I1", 'Ссылка в магазин');

        $sheet->getColumnDimension('B')->setWidth(90);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(20);

        $products = $products = Product::with('price')->get();

        $row_number = 2;

        foreach ($products as $key => $product) {
            $prices = $product->price()
                ->where('report_id', $id)
                ->orderBy('price', 'asc')
                ->get();
            //dd($prices);
            $color = 'e2efda';
            if ($key % 2 == 0) $color = 'a9d08e';

            $style = array(
                'borders' => array(
                    'bottom'     => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            '	rgb' => '000000'
                        )
                    ),
                    'top'     => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'left'     => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    ),
                    'right'     => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => '000000'
                        )
                    )
                ),
                'fill' => array(
                    'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                    'rotation'   => 0,
                    'color'   => array(
                        'rgb' => $color
                    )
                )
            );

            for ($r = 0; $r < count($prices); $r++){
                $sheet->setCellValueByColumnAndRow(0, ($row_number + $r), $product->SKU);
                $sheet->getStyleByColumnAndRow(0, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(1, ($row_number + $r), $product->Name);
                $sheet->getCell('B' . ($row_number + $r))->getHyperlink()->setUrl($product->Link);
                $sheet->getCell('B' . ($row_number + $r))->getHyperlink()->setTooltip('Navigate to website');
                $sheet->getStyleByColumnAndRow(1, $row_number + $r)->applyFromArray($style);
                $sheet->getStyleByColumnAndRow(1, $row_number + $r)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                $sheet->getStyleByColumnAndRow(1, $row_number + $r)->getFont()->getColor()->applyFromArray(array('rgb' => '0000FF'));

                $sheet->setCellValueByColumnAndRow(2, ($row_number + $r), $product->Price);
                $sheet->getStyleByColumnAndRow(2, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(3, ($row_number + $r), ($product->Price - ($product->Price * 0.05)));
                $sheet->getStyleByColumnAndRow(3, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(4, ($row_number + $r), $prices[$r]->store);
                $sheet->getStyleByColumnAndRow(4, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(5, ($row_number + $r), $prices[$r]->price);
                $sheet->getStyleByColumnAndRow(5, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(6, ($row_number + $r), (($prices[$r]->price - $product->Price) / $product->Price));
                $sheet->getStyleByColumnAndRow(6, $row_number + $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE_00);
                $sheet->getStyleByColumnAndRow(6, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(7, ($row_number + $r), $prices[$r]->date);
                $sheet->getStyleByColumnAndRow(7, $row_number + $r)->applyFromArray($style);

                $sheet->setCellValueByColumnAndRow(8, ($row_number + $r), 'В магазин');
                $sheet->getCell('I' . ($row_number + $r))->getHyperlink()->setUrl($prices[$r]->link);
                $sheet->getCell('I' . ($row_number + $r))->getHyperlink()->setTooltip('Navigate to website');
                $sheet->getStyleByColumnAndRow(8, $row_number + $r)->applyFromArray($style);
                $sheet->getStyleByColumnAndRow(8, $row_number + $r)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
            }
            $row_number += $r;
        }
        $this->downloadReport($xls, $id);
    }


    public function downloadReport($xls, $id)
    {
        $name = Report::findOrFail($id);
        // Выводим HTTP-заголовки
        header ( "Expires:" . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename={$name->name}.xls" );

        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('php://output');
    }

}
