<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware'=>['auth']], function (){
    Route::resource('/products', 'ProductController');

    Route::get('/import', 'ExcelController@index')->name('page-import');
    Route::post('/import', 'ExcelController@import')->name('import');

    //Route::get('start-parser/', 'ParserController@start')->name('parser.start');

    Route::post('/home', 'ParserController@start')->name('parser.start');

    Route::resource('/reports', 'ReportController');

    Route::get('/download/{report}', 'ExcelController@mySqlToExcel')->name('download');
});