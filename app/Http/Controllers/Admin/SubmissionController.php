<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(): View
    {
        $submissions = ContactSubmission::query()
            ->latest()
            ->paginate(25);

        // Opening the list is what marks them read — there is no separate
        // detail view, the messages are short enough to show inline.
        ContactSubmission::query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.submissions.index', ['submissions' => $submissions]);
    }

    public function destroy(ContactSubmission $submission): RedirectResponse
    {
        $submission->delete();

        return back()->with('status', 'تم حذف الرسالة.');
    }
}
