<?php

namespace App\Http\Controllers\Accounts;

use App\Models\AccHead;
use Illuminate\Http\Request;
use App\Models\AccTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ACCHeadController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $heads = AccHead::query()
            ->when(filled($search), function ($query) use ($search) {
                $query->where('head', 'LIKE', '%' . $search . '%')
                      ->orWhere('sub_head', 'LIKE', '%' . $search . '%')
                      ->orWhere('parent_head', 'LIKE', '%' . $search . '%');
            })
            ->paginate(15);

        return view('backend.accounts.heads.index', compact('heads', 'search'));
    }

    public function create()
    {
        return view('backend.accounts.heads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'head' => 'required',
            'parent_head' => 'required'
        ]);

        $head = new AccHead;
        $head->parent_head = $request->parent_head;
        $head->sub_head = $request->sub_head;
        $head->head = $request->head;
        $head->reference_id = NULL;
        $head->reference_type = AccHead::class;
        $head->user_id = $request->user()->id ?? 0;
        if ($head->save()) {
            flash(('Head created successfully'))->success();
            return redirect()->route('heads.index');
        }else{
            flash(('Something went wrong!'))->error();
            return redirect()->route('heads.index');
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $head = AccHead::find($id);
        return view('backend.accounts.heads.edit', compact('head'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'head' => 'required',
            'parent_head' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $head = AccHead::with('transactions')->findOrFail($id);

            $originalHead = $head->head;

            $head->parent_head = $request->parent_head;
            $head->sub_head = $request->sub_head;
            $head->head = $request->head;

            $headChanged = $originalHead !== $request->head;
            if($head->reference_type !== 'App\Models\Supplier') {
                $head->head = $request->head;
                if ($head->isDirty('head')) {
                    $head->transactions()->update(['head' => $head->head]);
                }
            }
            if ($headChanged) {
                AccTransaction::where('head', $originalHead)->update(['head' => $request->head]);
                AccTransaction::where('head_id', $head->id)
                    ->where('head_type', AccHead::class)
                    ->update(['head' => $request->head]);
            }

            if ($head->save()) {
                DB::commit();
                flash(('Head updated successfully'))->success();
            } else {
                throw new \Exception('Failed to update account head for ID: ' . $id);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            flash(('Something went wrong!'))->error();
        } finally {
            return redirect()->route('heads.index');
        }

    }

    public function destroy($id)
    {
        AccHead::find($id)->delete();

        flash(('Head deleted successfully'))->success();
        return redirect()->route('heads.index');
    }

    public function get_subheads(Request $request){
        $subheads = AccHead::select('sub_head as shead')->where('parent_head', $request->parent_head)->groupBy('sub_head')->get();
        if(empty($sub_heads)){
            $subheads = AccHead::select('head as shead')->where('parent_head', $request->parent_head)->groupBy('head')->get();
        }

        $html = '<option value="">'.("Select Subhead").'</option>';
        foreach ($subheads as $shead) {
            $html .= '<option value="' . $shead->shead . '">' . $shead->shead . '</option>';
        }
        $html = json_encode($html);

        return response()->json(array('data' => $subheads, 'html'=>$html));
    }
}
