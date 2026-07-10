<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class QueryController extends Controller
{
    public function query(){
        $data = DB::table("products")
        ->select("products.*",
                  DB::raw("(SELECT SUM(products_stock.stock) FROM products_stock
                              WHERE products_stock.product_id = products.id
                              GROUP BY products_stock.product_id) as product_stock"),
                  DB::raw("(SELECT SUM(products_sell.sell) FROM products_sell
                              WHERE products_sell.product_id = products.id
                              GROUP BY products_sell.product_id) as product_sell"))
        ->get();
    }
}
