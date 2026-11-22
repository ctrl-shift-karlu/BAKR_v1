<?php

namespace App\Http\Controllers\Scholar;

use App\Http\Controllers\Controller;
use App\Models\ScholarRequirement;
use App\Support\MaintainingDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Scholar portal › Requirements: upload each term's maintaining documents and open them again.
 * Files go to the private `local` disk under scholar-requirements/{scholar}/ with a random name;
 * they are only ever streamed back to the owner or to that scholarship's provider.
 * Every requirement is looked up through the signed-in scholar's own records (rule 4).
 */
class ScholarRequirementController extends Controller
{
    /**
     * Upload or replace one requirement's file. The upload dialog sends it with Accept: application/json
     * (real progress bar; errors come back as 422 JSON and are shown in the dialog); a plain form post
     * (no JavaScript) redirects back to that document's row.
     */
    public function upload(Request $request, int $requirement): RedirectResponse|JsonResponse
    {
        $row = $this->own($request, $requirement);
        abort_if($row->status === 'approved', 409, 'This requirement is already approved.');
        // A terminated scholarship takes no more documents (only its provider can reinstate it).
        abort_if($row->scholar->isTerminated(), 403, 'This scholarship was terminated by your provider.');
        // The provider does not collect renewal documents through BAKR.
        abort_unless($row->scholar->scholarship->offers('requirements'), 404);

        // The provider's own settings for this document (My scholarships › Maintaining documents).
        $types = $row->acceptedTypes();
        $mb = rtrim(rtrim(number_format($row->maxKb() / 1024, 1), '0'), '.');
        $request->validate([
            'file' => ['required', 'file', 'mimes:'.implode(',', MaintainingDocument::extensions($types)), 'max:'.$row->maxKb()],
        ], [
            'file.mimes' => 'Upload a '.MaintainingDocument::typesLabel($types).' file.',
            'file.max' => "The file must be {$mb} MB or smaller.",
        ]);

        $file = $request->file('file');
        $path = $file->storeAs('scholar-requirements/'.$row->scholar_id, Str::random(40).'.'.$file->extension(), 'local');

        if ($row->hasFile()) {
            Storage::disk('local')->delete($row->file_path);
        }

        $row->update([
            'file_path' => $path,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 190, ''),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'status' => 'submitted',
            'submitted_at' => now(),
            'reviewed_at' => null,
            'review_note' => null,
        ]);

        $message = $row->name.' was sent to '.$row->scholar->scholarship->provider.'. It is now waiting for their review.';

        if ($request->expectsJson()) {
            session()->flash('status', $message);   // shown after the page reloads

            return response()->json(['message' => $message, 'id' => $row->id, 'status' => $row->status]);
        }

        return redirect()->to(url()->previous().'#req-'.$row->id)->with('status', $message);
    }

    /** Download, or with ?inline=1 show it in the preview (PDF / JPG / PNG only, type fixed, no sniffing). */
    public function download(Request $request, int $requirement): StreamedResponse
    {
        $row = $this->own($request, $requirement);
        abort_unless($row->hasFile() && Storage::disk('local')->exists($row->file_path), 404);
        $name = $row->original_name ?: basename($row->file_path);

        $inlineTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if ($request->boolean('inline') && in_array($row->mime, $inlineTypes, true)) {
            return Storage::disk('local')->response($row->file_path, $name, [
                'Content-Type' => $row->mime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        return Storage::disk('local')->download($row->file_path, $name);
    }

    /** A requirement that belongs to one of the signed-in scholar's own records. */
    private function own(Request $request, int $id): ScholarRequirement
    {
        return ScholarRequirement::whereKey($id)
            ->whereHas('scholar', fn ($q) => $q->where('user_id', $request->user()->id))
            ->firstOrFail();
    }
}
