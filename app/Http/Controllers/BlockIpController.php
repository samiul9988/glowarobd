<?php

namespace App\Http\Controllers;

use App\Models\BlockIp;
use Illuminate\Http\Request;

class BlockIpController extends Controller
{
    public function index()
    {
        $blockIps = BlockIp::with('user')->latest()->paginate(10);
        return view("backend.setup_configurations.block_ip.index", compact('blockIps'));
    }

    public function destroy($id)
    {
        BlockIp::where('id', $id)->delete();
        flash(('Ip address remove from blacklist has been successfully'))->success();
        return redirect()->route('block.ip.index');
    }

    public function blockedIp()
    {
        return view('frontend.ip_blocked');
    }
}
