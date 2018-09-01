<?php

namespace App\Http\Controllers;

use App\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
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
    public function index(Request $request)
    {
        if ($request->st == 'f') {
            Session::flash('status', 'Парсинг хотлайна успешно завершен.');
        }
        return view('home', [
            'reports' => DB::table('reports')
                ->orderBy('id', 'desc')
                ->get()
        ]);
    }

}
