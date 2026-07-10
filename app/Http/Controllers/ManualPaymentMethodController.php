<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManualPaymentMethodController extends Controller
{
    public function index() { return view('errors.404'); }
    public function create() { return view('errors.404'); }
    public function store(Request $request) { return redirect()->back(); }
    public function show($id) { return view('errors.404'); }
    public function edit($id) { return view('errors.404'); }
    public function update(Request $request, $id) { return redirect()->back(); }
    public function destroy($id) { return redirect()->back(); }
    public function show_payment_modal(Request $request) { return view('errors.404'); }
    public function submit_offline_payment(Request $request) { return redirect()->back(); }
    public function offline_recharge_modal(Request $request) { return redirect()->back(); }
    public function offline_customer_package_purchase_modal(Request $request) { return redirect()->back(); }
    public function offline_seller_package_purchase_modal(Request $request) { return redirect()->back(); }
}
