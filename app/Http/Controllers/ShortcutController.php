<?php

namespace App\Http\Controllers;

use App\Models\Shortcut;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShortcutController extends Controller
{
    public function index()
    {
        $shortcuts = \App\Models\Shortcut::with('module:id,name')->get();
        return response()->json($shortcuts);
    }

    public function store(Request $request)
    {
        try{
            // dd($request->all());
            $payload = $request->validate([
                'labels' => 'required|array',
                'labels.*' => 'required|string|max:255',
                'urls' => 'required|array',
                'urls.*' => 'required|string',
                'icons' => 'required|array',
                'icons.*' => 'nullable|integer',
                'modules' => 'required|array',
                'modules.*' => 'required|exists:shortcut_modules,id',
                'ids' => 'required|array',
                'ids.*' => 'nullable|integer|exists:shortcuts,id',
            ]);

            // dd($payload);
            // Get all existing module IDs to detect deletions
            $existingIds = Shortcut::pluck('id')->toArray();
            $submittedIds = [];

            foreach ($request->ids as $index => $id) {
                $data = [
                    'shortcut_module_id' => $request->modules[$index],
                    'name' => $request->labels[$index],
                    'icon' => $request->icons[$index],
                    'url' => $request->urls[$index],
                ];

                if (!empty($id)) {
                    // Update existing module
                    Shortcut::where('id', $id)->update($data);
                    $submittedIds[] = $id;
                } else {
                    // Create new module
                    $newShortcut = Shortcut::create($data);
                    $submittedIds[] = $newShortcut->id;
                }
            }

            // Delete modules that weren't submitted in the form
            $idsToDelete = array_diff($existingIds, $submittedIds);
            if (!empty($idsToDelete)) {
                Shortcut::whereIn('id', $idsToDelete)->delete();
            }

            flash(('Shortcuts have been inserted successfully'))->success();
            return redirect()->back();
        } catch (ValidationException $e) {
            $error = $e->validator->errors()->first();
            flash($error)->error();
            return redirect()->back()->withInput();
        } catch (\Exception $e){
            flash('Server Error')->error();
            return redirect()->back();
        }
    }
}
