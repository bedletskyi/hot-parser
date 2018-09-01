<?php

namespace App\Http\Controllers;

use App\Product;
use App\Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Font;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Writer_Excel5;


class ExcelController extends Controller
{
    /**
     * Обьект PHPExcel
     *
     * @var object
     */
    private $xls;

    /**
     * Название колонок которые будут вносится в базу данных
     *
     * @var array
     */
    public $cells = array(
        'A'=>'SKU',
        'B'=>'Name',
        'C'=>'Price',
        'D'=>'Link'
    );

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('products.import');
    }

    /**
     * Импортируем и сохраняем данные из файла в базу
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        $this->validation($request);

        // Удаляем все записи из таблицы
        if ($request->get('reset_all')) {
            DB::table('products')->delete();
        }

        $data_arr =  $this->excelToMySql($request->file('file'));

        $this->save($data_arr);

        return redirect()->route('products.index')->with('message', 'Позиции успешно добавлены');
    }

    /**
     * Преобразовываем даные из фала excel в массив
     *
     * @param $file
     * @return array
     */
    public function excelToMySql($file)
    {
        $this->getPhpExcel($file);

        $this->xls->setActiveSheetIndex(0);
        $sheet = $this->xls->getActiveSheet();

        $rowIterator = $sheet->getRowIterator();

        $arr = [];

        foreach ($rowIterator as $row) {
            if ($row->getRowIndex() != 1) {
                $cellIterator = $row->getCellIterator();
                foreach ($cellIterator as $cell) {
                    $cellPath = $cell->getColumn();
                    if (isset($this->cells[$cellPath])) {
                        $arr[$row->getRowIndex()][$this->cells[$cellPath]] = $cell->getCalculatedValue();
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * Записываем массив данных в базу
     *
     * @param array $data
     * @return bool
     */
    public function save(array $data)
    {
        foreach ($data as $field){
            Product::create($field);
        }
        return true;
    }

    /**
     * Получаем обьект PHPExcel
     *
     * @param $file
     * @return \PHPExcel
     */
    public function getPhpExcel($file)
    {
        $php_excel = PHPExcel_IOFactory::load($file);
        return $this->xls = $php_excel;
    }

    /**
     * Проверяем файл на соотвецтвие типу
     *
     * @param $data
     * @return mixed
     */
    public function validation($data)
    {

        return Validator::make($data->all(), [
            'file' => 'mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])->validate();

    }

    public function mySqlToExcel(Report $report)
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
                ->where('report_id', $report->id)
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
        $this->downloadReport($xls);
    }

    public function downloadReport($xls)
    {
        // Выводим HTTP-заголовки
        header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
        header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
        header ( "Cache-Control: no-cache, must-revalidate" );
        header ( "Pragma: no-cache" );
        header ( "Content-type: application/vnd.ms-excel" );
        header ( "Content-Disposition: attachment; filename=matrix.xls" );

        // Выводим содержимое файла
        $objWriter = new PHPExcel_Writer_Excel5($xls);
        $objWriter->save('php://output');
    }

    public function getData()
    {
        $products = Product::with('price')->get();
        return $products;
    }
}
