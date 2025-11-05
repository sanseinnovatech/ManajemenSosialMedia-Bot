<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaAccount;
use App\Models\PostLog;
use Illuminate\Http\Request;
use App\Jobs\PostToSocialMedia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PostLogsExport;

class SocialMediaPostController extends Controller
{
    public function index()
    {
        $accounts = SocialMediaAccount::all();
        return view('dashboard', compact('accounts'));
    }

    public function post(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'media' => 'nullable|file',
            'accounts' => 'required|array',
        ]);

        $path = $request->file('media')?->store('uploads', 'public');

        foreach ($request->accounts as $id) {
            $acc = SocialMediaAccount::findOrFail($id);
            PostToSocialMedia::dispatch($acc, $request->message, $path);
        }

        return back()->with('success', 'Posts sedang dikirim...');
    }

    public function export()
    {
        return Excel::download(new PostLogsExport, 'post-logs.xlsx');
    }
}

