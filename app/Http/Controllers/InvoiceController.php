<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use Session;
use PDF;
use Config;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    //download invoice
    public function invoice_download($id)
    {
        if(Session::has('currency_code')){
            $currency_code = Session::get('currency_code');
        }
        else{
            $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        }
        $language_code = Session::get('locale', Config::get('app.locale'));

        if(Language::where('code', $language_code)->first()->rtl == 1){
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        }else{
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
        }

        if($currency_code == 'BDT' || $language_code == 'bd'){
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        }elseif($currency_code == 'KHR' || $language_code == 'kh'){
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        }elseif($currency_code == 'AMD'){
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        }elseif($currency_code == 'ILS'){
            // Israeli font
            $font_family = "'Varela Round','sans-serif'";
        }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' || $currency_code == 'ROM'){
            // middle east/arabic font
            $font_family = "'XBRiyaz','sans-serif'";
        }else{
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }

        $order = Order::with('payments')->findOrFail($id);

        // return view('backend.invoices.invoice', [
        //     'order' => $order,
        //     'font_family' => $font_family,
        //     'direction' => $direction,
        //     'text_align' => $text_align,
        //     'not_text_align' => $not_text_align
        // ]);

        $pdf = PDF::loadView(env('THEME_NAME').'frontend.invoices.invoice',[
            'order' => $order,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ], [], []);//->stream('order-'.$order->code.'.pdf');

        return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="order-'.$order->code.'.pdf"');
    }

    public function invoice_bulk_download() {
        $ids = explode(',', $_GET['ids']);
        if(Session::has('currency_code')){
            $currency_code = Session::get('currency_code');
        }
        else{
            $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        }
        $language_code = Session::get('locale', Config::get('app.locale'));

        if(Language::where('code', $language_code)->first()->rtl == 1){
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        }else{
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
        }

        if($currency_code == 'BDT' || $language_code == 'bd'){
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        }elseif($currency_code == 'KHR' || $language_code == 'kh'){
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        }elseif($currency_code == 'AMD'){
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        }elseif($currency_code == 'ILS'){
            // Israeli font
            $font_family = "'Varela Round','sans-serif'";
        }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' || $currency_code == 'ROM'){
            // middle east/arabic font
            $font_family = "'XBRiyaz','sans-serif'";
        }else{
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }
        $pdfview = view('frontend.invoices.invoice_bulk_head', [
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ]);
        if ($ids) {
            $i=0;
            foreach ($ids as $order_id) {
                $i++;
                $order = Order::findOrFail($order_id);

                $pdfview .= view('frontend.invoices.invoice_bulk', [
                    'order' => $order,
                    'ids' => $ids,
                    'counter' => $i,
                    'font_family' => $font_family,
                    'direction' => $direction,
                    'text_align' => $text_align,
                    'not_text_align' => $not_text_align
                ]);
            }
        }
        $pdfview .='</body>
        </html>';
        //echo $pdfview;exit;
        $pdf = PDF::loadHTML($pdfview);
        // return PDF::loadHTML($pdfview)->stream('all-order.pdf');
        return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="all-order.pdf"');

    }

    public function invoice_bulk_shipping_download() {
        $ids = explode(',', $_GET['ids']);
        if(Session::has('currency_code')){
            $currency_code = Session::get('currency_code');
        }
        else{
            $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        }
        $language_code = Session::get('locale', Config::get('app.locale'));

        if(Language::where('code', $language_code)->first()->rtl == 1){
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        }else{
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
        }

        if($currency_code == 'BDT' || $language_code == 'bd'){
            // bengali font
            $font_family = "'Hind Siliguri','sans-serif'";
        }elseif($currency_code == 'KHR' || $language_code == 'kh'){
            // khmer font
            $font_family = "'Hanuman','sans-serif'";
        }elseif($currency_code == 'AMD'){
            // Armenia font
            $font_family = "'arnamu','sans-serif'";
        }elseif($currency_code == 'ILS'){
            // Israeli font
            $font_family = "'Varela Round','sans-serif'";
        }elseif($currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' || $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' || $currency_code == 'ROM'){
            // middle east/arabic font
            $font_family = "'XBRiyaz','sans-serif'";
        }else{
            // general for all
            $font_family = "'Roboto','sans-serif'";
        }
        $pdfview = view('frontend.invoices.invoice_bulk_head', [
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ]);
        if ($ids) {
            $i=0;
            foreach ($ids as $order_id) {
                $i++;
                $order = Order::findOrFail($order_id);

        $pdfview .= view('frontend.invoices.shipping_label_bulk', [
            'order' => $order,
            'ids' => $ids,
            'counter' => $i,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ]);
            }
        }
        $pdfview .='</body>
        </html>';
        $pdf = PDF::loadHTML($pdfview);
        // return PDF::loadHTML($pdfview)->stream('all-order.pdf');
        return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="all-order.pdf"');

    }

    public function generateLabels(Request $request)
    {
        if(filled($request->id)){
            $orders = Order::with('packagedBy')->where('id', $request->id)->get();
            $bulk = false;
        }else{
            $ids = explode(',', $request->ids) ?? [];
            $orders = Order::with('packagedBy')->whereIn('id', $ids)->get();
            $bulk = true;
        }

        $pdf = PDF::loadView(config('app.theme').'.frontend.invoices.sticker_label', compact('orders', 'bulk'), [], [
                    'encoding' => 'UTF-8',
                    'format' => [76, 102], // 4in x 3in in mm
                    'margin-top' => 0,
                    'margin-right' => 0,
                    'margin-bottom' => 0,
                    'margin-left' => 0,
                    'dpi' => 300
                ]);

        if(filled($request->id)){
            return response()->json([
                'pdf' => base64_encode($pdf->output())
            ]);
        }else{
            return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="invoice-label.pdf"');
            // return $pdf->stream('invoice-label.pdf');
        }
    }

    public function posInvoiceLabel(Request $request, $id = null)
    {
        $order = Order::with('packagedBy', 'orderDetails.product')->findOrFail($id);
        $itemCount = $order->orderDetails->count();
        $baseHeight = 137;
        if(filled($order->coupon_discount) && $order->coupon_discount > 0){
            $baseHeight = 144;
        }
        $extraPerItem = 9;
        $height = $baseHeight + ($itemCount * $extraPerItem);

        // dd($height);
        // return view('frontend.invoices.pos_invoice_label', compact('order'));
        $pdf = PDF::loadView(config('app.theme').'.frontend.invoices.pos_invoice_label', compact('order'), [], [
                    'encoding' => 'UTF-8',
                    'format' => [95, $height],
                    'margin-top' => 0,
                    'margin-right' => 0,
                    'margin-bottom' => 0,
                    'margin-left' => 0,
                    'dpi' => 300
                ]);

    return response($pdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="pos-invoice-label.pdf"');
        // return $pdf->stream('pos-invoice-label.pdf');
    }
}
