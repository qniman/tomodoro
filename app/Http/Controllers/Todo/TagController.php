<?php

namespace App\Http\Controllers\Todo;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        return TagResource::collection($request->user()->tags()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string'],
        ]);

        $tag = $request->user()->tags()->create($validated);

        return new TagResource($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        $this->ensureOwnership($tag);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'color' => ['sometimes', 'string'],
        ]);

        $tag->update($validated);

        return new TagResource($tag);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $this->ensureOwnership($tag);

        $tag->delete();

        return response()->json([], 204);
    }

    protected function ensureOwnership(Tag $tag): void
    {
        abort_unless(auth()->id() === $tag->user_id, 403);
    }
}
