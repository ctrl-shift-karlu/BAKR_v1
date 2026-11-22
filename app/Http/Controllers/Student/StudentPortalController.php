<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarshipCatalogController;
use App\Models\ContactMessage;
use App\Models\ScholarshipApplication;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pages that keep students inside their portal instead of sending them to public pages:
 *  - scholarships: the full catalogue, with the student's own match status on each card
 *  - help: About BAKR, how it works, FAQs, and a message form (name/e-mail from the account)
 */
class StudentPortalController extends Controller
{
    public function scholarships(Request $request): View
    {
        $scholarships = ScholarshipCatalogController::activeScholarships();
        $current = ScholarshipApplication::where('user_id', $request->user()->id)->where('is_current', true)->first();

        return view('student.scholarships', [
            'sections' => ScholarshipCatalogController::sections($scholarships),
            'total' => $scholarships->count(),
            'matches' => $current ? $current->matches()->get()->keyBy('scholarship_id') : collect(),
        ]);
    }

    public function help(): View
    {
        return view('student.help', ['topics' => ContactMessage::TOPICS]);
    }

    public function message(Request $request, ContactMessageService $messages): RedirectResponse
    {
        $data = $this->validateHelpMessage($request, ContactMessage::TOPICS);

        // Name and e-mail come from the signed-in account, never from the form.
        $messages->record($data + [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);

        return $this->sent(redirect()->route('student.help'), 'Message sent', 'The BAKR team will reply to '.$request->user()->email.'.');
    }
}
