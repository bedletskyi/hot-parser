<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel_IOFactory;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
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



    public function index()
    {
        return view('product');
    }

    public function getData()
    {
        $data = Product::all();
        return $data;
    }

    public function store(Request $request)
    {
        $this->validation($request);

        $product = Product::create($request->all());
        return $product;
    }

    public function update(Request $request, $id)
    {
        $this->validation($request);

        $product = Product::findOrFail($id);
        $product->update($request->all());

        return $product;
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return;
    }

    public function import(Request $request)
    {
        Validator::make($request->all(), [
            'file' => 'mimetypes:application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])->validate();

        // Удаляем все записи из таблицы
        if ($request->get('reset')) {
            DB::table('products')->delete();
        }

        $data_arr =  $this->excelToMySql($request->file('file'));

        $this->save($data_arr);
        return 'ok';
    }

    public function validation($request)
    {
        $this->validate($request, [
            'SKU' => 'required',
            'Name' => 'required',
            'Price' => 'required|min:0|numeric',
            'Link' => 'required|url',
        ]);
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
}
