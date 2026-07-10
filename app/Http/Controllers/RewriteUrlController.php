<?php

namespace App\Http\Controllers;

use Cache;
use App\Models\RewriteUrl;
use Illuminate\Http\Request;
use Session;

class RewriteUrlController extends Controller
{
    public function index(Request $request)
    {
        $sort_search =null;
        $urls = RewriteUrl::query();
        if ($request->has('search')){
            $sort_search = $request->search;
            $urls = $urls->where(function ($q) use ($sort_search) {
                $q->where('url', 'like', '%'.$sort_search.'%')
                    ->orWhere('redirect_to', 'like', '%'.$sort_search.'%');
            });
        }
        $urls = $urls->paginate(15);
        return view('backend.rules.index', compact('urls', 'sort_search'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'url' => 'required|unique:rewrite_urls,url',
            'redirect_to' => 'required',
        ]);

        try{
            $url = new RewriteUrl;
            $url->url = $request->url;
            $url->redirect_to = $request->redirect_to;
            $url->save();

            return response()->json([
                'success' => true,
                'message' => ('URL rule created successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'url' => 'required|unique:rewrite_urls,url,'.$id,
            'redirect_to' => 'required',
        ]);

        try{
            $url = RewriteUrl::findOrFail($id);
            $url->url = $request->url;
            $url->redirect_to = $request->redirect_to;
            $url->save();

            return response()->json([
                'success' => true,
                'message' => ('URL rule updated successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update_status(Request $request, $id)
    {
        $url = RewriteUrl::findOrFail($id);
        $url->status = $request->status;
        $url->save();

        flash(('URL rule status updated successfully'))->success();
        return redirect()->route('rewrite_url.index');
    }

    public function destroy($id)
    {
        $url = RewriteUrl::findOrFail($id);
        $url->delete();

        flash(('URL rule deleted successfully'))->success();
        return redirect()->route('rewrite_url.index');
    }
}
