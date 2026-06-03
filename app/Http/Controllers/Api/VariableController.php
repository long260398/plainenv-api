<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Variable\StoreVariableRequest;
use App\Http\Requests\Variable\UpdateVariableRequest;
use App\Http\Resources\VariableResource;
use App\Models\Environment;
use App\Models\Project;
use App\Models\Variable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariableController extends Controller
{
    public function index(Request $request, Project $project, Environment $environment): JsonResponse
    {
        $this->authorize('view', $project);
        abort_if($environment->project_id !== $project->id, 404);

        return response()->json([
            'data' => VariableResource::collection($environment->variables()->orderBy('key')->get()),
        ]);
    }

    public function store(StoreVariableRequest $request, Project $project, Environment $environment): JsonResponse
    {
        $this->authorize('update', $project);
        abort_if($environment->project_id !== $project->id, 404);

        $variable = $environment->variables()->create($request->validated());

        $this->log($project->id, $request->user()->id, 'variable.created', [
            'environment' => $environment->name,
            'key'         => $variable->key,
        ]);

        return response()->json(['data' => new VariableResource($variable)], 201);
    }

    public function update(UpdateVariableRequest $request, Project $project, Environment $environment, Variable $variable): JsonResponse
    {
        $this->authorize('update', $project);
        abort_if($environment->project_id !== $project->id, 404);
        abort_if($variable->environment_id !== $environment->id, 404);

        $variable->update($request->validated());

        $this->log($project->id, $request->user()->id, 'variable.updated', [
            'environment' => $environment->name,
            'key'         => $variable->key,
        ]);

        return response()->json(['data' => new VariableResource($variable)]);
    }

    public function destroy(Request $request, Project $project, Environment $environment, Variable $variable): JsonResponse
    {
        $this->authorize('update', $project);
        abort_if($environment->project_id !== $project->id, 404);
        abort_if($variable->environment_id !== $environment->id, 404);

        $key = $variable->key;
        $variable->delete();

        $this->log($project->id, $request->user()->id, 'variable.deleted', [
            'environment' => $environment->name,
            'key'         => $key,
        ]);

        return response()->json(null, 204);
    }

    private function log(int $projectId, int $userId, string $action, array $meta): void
    {
        \App\Models\ActivityLog::create([
            'project_id' => $projectId,
            'user_id'    => $userId,
            'action'     => $action,
            'meta'       => $meta,
        ]);
    }
}
