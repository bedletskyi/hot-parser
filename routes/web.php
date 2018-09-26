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


Auth::routes();

Route::group(['middleware'=>['auth']], function (){
    Route::get('/', 'HomeController@index')->name('home');
    Route::get('/product', 'ProductController@index')->name('product');
    Route::get('/product/get', 'ProductController@getData')->name('getData');
    Route::post('/product/store', 'ProductController@store')->name('product.store');
    Route::delete('/product/delete/{id}', 'ProductController@destroy')->name('product.destroy');
    Route::patch('/product/edit/{id}', 'ProductController@update')->name('product.update');
    Route::post('/product/import', 'ProductController@import')->name('product.import');

    Route::get('/report/get', 'HomeController@getData')->name('report.get');
    Route::get('/report/{id}', 'HomeController@show')->name('report.show');
    Route::delete('/report/delete/{id}', 'HomeController@destroy')->name('report.destroy');
    Route::get('/report/download/{id}', 'HomeController@dowload')->name('report.download');

    Route::get('/start', 'HomeController@startParser')->name('parsing');

});

