<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\Task;
use App\Models\Document;
use App\Models\Attachment;

class AttachmentController extends Controller
{
    public function show(Request $request, Task $task, Attachment $attachment) {
        // $this->authorize('viewAttachment', $task);

        return Storage::get($attachment->path);
    }

    public function store(Request $request, Document $document) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort (403);
        
        if ($request->hasFile('attachments')) foreach ($request->file('attachments') as $attachment) {
            $path = $attachment->store('attachments');

            $document->attachments()->create([
                'name' => $attachment->getClientOriginalName(),
                'path' => $path,
            ]);
        }

        return response('', 201);
    }

    public function destroy(Request $request, Document $document, Attachment $attachment) {
        if (!$request->user()->can(['taskman_modify_documents'])) abort (403);

        Storage::delete($attachment->path);
        return $attachment->delete();
    }

    public function showDocument(Request $request, Document $document, Attachment $attachment) {
      // if (!$request->user()->can(['taskman_read_documents'])) abort (403);

      return Storage::get($attachment->path);
    }
}
