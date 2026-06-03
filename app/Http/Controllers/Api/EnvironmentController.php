<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Environment\StoreEnvironmentRequest;
use App\Http\Resources\EnvironmentResource;
use App\Models\Environment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnvironmentController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'data' => EnvironmentResource::collection($project->environments),
        ]);
    }

    public function store(StoreEnvironmentRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $environment = $project->environments()->create($request->validated());

        return response()->json(['data' => new EnvironmentResource($environment)], 201);
    }

    public function destroy(Request $request, Project $project, Environment $environment): JsonResponse
    {
        $this->authorize('update', $project);

        abort_if($environment->project_id !== $project->id, 404);

        $environment->delete();

        return response()->json(null, 204);
    }

    public function compare(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'from' => ['required', 'integer', 'exists:environments,id'],
            'to'   => ['required', 'integer', 'exists:environments,id'],
        ]);

        $from = $project->environments()->with('variables')->findOrFail($request->from);
        $to   = $project->environments()->with('variables')->findOrFail($request->to);

        $fromKeys = $from->variables->keyBy('key');
        $toKeys   = $to->variables->keyBy('key');
        $allKeys  = $fromKeys->keys()->merge($toKeys->keys())->unique()->sort()->values();

        $diff = $allKeys->map(function ($key) use ($fromKeys, $toKeys) {
            $inFrom = $fromKeys->has($key);
            $inTo   = $toKeys->has($key);

            if ($inFrom && $inTo) {
                $status = $fromKeys[$key]->value === $toKeys[$key]->value ? 'same' : 'different';
            } elseif ($inFrom) {
                $status = 'missing_in_to';
            } else {
                $status = 'missing_in_from';
            }

            return ['key' => $key, 'status' => $status];
        });

        return response()->json([
            'from' => $from->name,
            'to'   => $to->name,
            'diff' => $diff,
        ]);
    }

    public function export(Request $request, Project $project, Environment $environment): \Illuminate\Http\Response
    {
        $this->authorize('view', $project);

        abort_if($environment->project_id !== $project->id, 404);

        $lines = $environment->variables
            ->map(fn($v) => "{$v->key}={$v->value}")
            ->join("\n");

        return response($lines, 200)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename=".env"');
    }
}
