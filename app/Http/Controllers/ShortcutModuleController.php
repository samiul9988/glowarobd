<?php
namespace App\Http\Controllers;

use App\Models\ShortcutModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortcutModuleController extends Controller
{
    public function index()
    {
        $modules = \App\Models\ShortcutModule::with('shortcuts')->get();
        return response()->json($modules);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'module_ids' => 'required|array',
            'module_ids.*' => 'nullable|integer|exists:shortcut_modules,id',
            'modules' => 'required|array',
            'modules.*' => 'required|string|max:255',
            'module_dashboards' => 'required|array',
            'module_dashboards.*' => 'required|string|in:customer_care_dashboard,packaging_dashboard,account_inventory_dashboard',
            'module_statuses' => 'required|array',
            'module_statuses.*' => 'required|boolean',
        ]);
// dd($validated);
        // Get all existing module IDs to detect deletions
        $existingModuleIds = ShortcutModule::pluck('id')->toArray();
        $submittedIds = [];

        foreach ($request->module_ids as $index => $moduleId) {
            $data = [
                'name' => $request->modules[$index],
                'dashboard' => $request->module_dashboards[$index],
                'status' => $request->module_statuses[$index],
            ];

            if (!empty($moduleId)) {
                // Update existing module
                ShortcutModule::where('id', $moduleId)->update($data);
                $submittedIds[] = $moduleId;
            } else {
                // Create new module
                $newModule = ShortcutModule::create($data);
                $submittedIds[] = $newModule->id;
            }
        }

        // Delete modules that weren't submitted in the form
        $idsToDelete = array_diff($existingModuleIds, $submittedIds);
        if (!empty($idsToDelete)) {
            ShortcutModule::whereIn('id', $idsToDelete)->delete();
        }

        flash(('Modules have been inserted successfully'))->success();
        // Redirect back with success message
        return redirect()->back();
    }
}
