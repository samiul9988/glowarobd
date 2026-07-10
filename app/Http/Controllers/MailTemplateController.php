<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    public function index()
    {
        $mail_templates = MailTemplate::select(['id','type','subject','status'])->get();
        return view('backend.setup_configurations.mail_template.index',compact('mail_templates'));
    }

    public function edit($id)
    {
        $mail_template = MailTemplate::findOrFail($id);
        return view('backend.setup_configurations.mail_template.edit',compact('mail_template'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:mail_templates,id',
            'subject' => 'required',
            'content' => 'required'
        ]);
        MailTemplate::where('id',$request->id)->update([
            'subject' => strval($request->subject),
            'content' => strval($request->content),
        ]);

        flash(('Template updated successfully'))->success();
        return back();
    }

    public function update_status(Request $request)
    {
        $mail_templates = MailTemplate::findOrFail($request->id);
        $mail_templates->status = $request->status;
        if ($mail_templates->save()) {
            // flash(('Template status updated successfully'))->success();
            return 1;
        }
        return 0;
    }
}
