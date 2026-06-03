<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\InviteMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $members = $project->members()->with('user')->get();

        return response()->json(['data' => MemberResource::collection($members)]);
    }

    public function store(InviteMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($project->user_id === $user->id) {
            return response()->json(['message' => 'User is already the project owner'], 422);
        }

        $member = $project->members()->updateOrCreate(
            ['user_id' => $user->id],
            ['role' => $request->role]
        );

        $member->load('user');

        return response()->json(['data' => new MemberResource($member)], 201);
    }

    public function destroy(Request $request, Project $project, User $user): JsonResponse
    {
        $this->authorize('delete', $project);

        if ($project->user_id === $user->id) {
            return response()->json(['message' => 'Cannot remove the project owner'], 422);
        }

        $project->members()->where('user_id', $user->id)->delete();

        return response()->json(null, 204);
    }
}
