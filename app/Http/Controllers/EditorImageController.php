<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Support\ImageStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorImageController extends Controller
{
    /**
     * Store a photo dropped into the text editor (re-encoded to WebP like every other upload).
     */
    public function store(Request $request, ImageStore $images): JsonResponse
    {
        abort_unless($request->user()->canAny(['services.manage', 'projects.manage', 'faqs.manage', 'content.manage']), 403);

        $request->validate(['image' => ImageStore::rules(200, 150)]);

        $path = $images->store($request->file('image'), 'editor', 1600);
        ActivityLog::record('media.uploaded', "Uploaded editor photo {$path}");

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }
}
