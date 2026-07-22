<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $media = Media::query()
            ->when($request->string('q')->trim()->value(), fn ($q, $term) => $q
                ->where('original_name', 'like', "%{$term}%")
                ->orWhere('alt_ar', 'like', "%{$term}%"))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        // The same endpoint backs the in-form image picker, which fetches
        // JSON rather than loading a second page.
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $media->through(fn (Media $m) => [
                    'id' => $m->id,
                    'url' => $m->url(),
                    'alt' => $m->alt_ar,
                    'name' => $m->original_name,
                    'width' => $m->width,
                    'height' => $m->height,
                ]),
            ]);
        }

        return view('admin.media.index', ['media' => $media]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            // Explicit mimes rather than the generic `image` rule: this list
            // is what the site can actually render, and it excludes SVG,
            // which can carry script and would be served from our own origin.
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'alt_ar' => ['nullable', 'string', 'max:255'],
        ], [
            'file.mimes' => 'الصيغ المسموحة: JPG، PNG، WebP، AVIF.',
            'file.max' => 'أقصى حجم للملف 5 ميجابايت.',
        ]);

        $file = $request->file('file');

        // Stored under a random name: the original may contain Arabic,
        // spaces and punctuation that make for fragile URLs. The readable
        // name is kept in the database for the library listing.
        $name = Str::random(24).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/'.now()->format('Y/m'), $name, 'public');

        $dimensions = @getimagesize($file->getRealPath());

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt_ar' => $request->string('alt_ar')->trim()->value() ?: null,
            'uploaded_by' => $request->user()->id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $media->id,
                'url' => $media->url(),
                'alt' => $media->alt_ar,
            ], 201);
        }

        return back()->with('status', 'تم رفع الملف.');
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $medium->update($request->validate([
            'alt_ar' => ['nullable', 'string', 'max:255'],
            'caption_ar' => ['nullable', 'string', 'max:500'],
        ]));

        return back()->with('status', 'تم تحديث بيانات الملف.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        // The file goes with the row — an orphaned upload is invisible to the
        // client but still occupies the disk.
        Storage::disk($medium->disk)->delete($medium->path);

        $medium->delete();

        return back()->with('status', 'تم حذف الملف.');
    }
}
