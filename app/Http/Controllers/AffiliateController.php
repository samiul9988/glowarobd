<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AffiliateController extends Controller
{
    public function index() { return view('errors.404'); }
    public function affiliate_option_store(Request $request) { return redirect()->back(); }
    public function configs() { return view('errors.404'); }
    public function config_store(Request $request) { return redirect()->back(); }
    public function users() { return view('errors.404'); }
    public function show_user(Request $request) { return view('errors.404'); }
    public function store_user(Request $request) { return redirect()->back(); }
    public function payments() { return view('errors.404'); }
    public function payment_store(Request $request) { return redirect()->back(); }
    public function pay(Request $request) { return redirect()->back(); }
}
