<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\CollectionDesign;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CollectionDesignController extends Controller
{
    public function index(){}

    public function store(Request $request){
        try {
            // dd($request);

            $this->validate($request,[
                "design_name"=>"required|string|max:100|unique:collection_designs,title",
                "design_image"=>"required|integer",
                "design_file_name"=>"required|string|max:100",
            ]);

            $data = CollectionDesign::create([
                    'title' => $request->input('design_name'),
                    'image' => $request->input('design_image'),
                    'file_name' => $request->input('design_file_name')
                ]);

                Cache::flush();
                flash(("Settings updated successfully"))->success();
                return back();
            /*
            if ($request->has('design_image')) {
                $image = $request->file('design_image');
                $fileNameToStore = 'design-'.md5(uniqid()).time().'.'.$image->getClientOriginalExtension();


                $data = CollectionDesign::create([
                    'title' => $request->input('design_name'),
                    'image' => 'images/design/'.$fileNameToStore,
                    'file_name' => $request->input('design_file_name')
                ]);

                if($data){
                    $image->move(public_path('images/design'), $fileNameToStore);
                }

                Cache::flush();
                flash(("Settings updated successfully"))->success();
                return back();
            } else {
                return response()->json(['failed', 'Image is required.']);
            }
            */
        }catch(QueryException $e){
            if($e->errorInfo[1] == 1062)
                flash(("Design already exists"))->error();
            return back();
        } catch (Exception $e) {
            dd($e->getMessage());
            flash(("Request failed!"))->error();
            return back();
        }
    }

    public function show(){
        return CollectionDesign::all();
    }
}
