<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Traits\ApiResponseTrait;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
     * 
     * @OA\Get(
     *      path="/api/project",
     *      summary="Liste tous les projets",
     *      operationId="getprojects",
     *      tags={"Projects"},
     *      @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         @OA\Schema(type="string", example="application/vnd.api+json")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Liste des projets",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(ref="#/definitions/Item")
     *          )
     *      )
     * )
     */
    public function index()
    {
        $now = new \DateTime();

//-------------------------------------------------------------------------------------------------------------
        // Récupérer tous les projets au-delà d'aujourd'hui
        // $projects = Project::whereDate('start_date', '>', $now)->get();

        // Récupérer tous les projets en-deçà d'aujourd'hui
        // $projects = Project::whereDate('end_date', '<', $now)->get();

        // Récupérer tous les projets compris entre ... et ...
        // $projects = Project::whereBetween('start_date', ['2025-01-15', '2025-08-01'])
        //                     ->orWhereBetween('end_date', ['2025-05-01', '2025-09-01'])
        //                     ->get();
//-------------------------------------------------------------------------------------------------------------

        // Trier les projets
        // $projects = Project::orderBy('created_at', 'ASC')->get();

        // $projects = Project::orderBy('rate', 'DESC')
        //                     ->orderBy('start_date', 'DESC')
        //                     ->get();

//-------------------------------------------------------------------------------------------------------------

        // Pagination
        // $projects = Project::paginate(2);

//-------------------------------------------------------------------------------------------------------------

        // En fonction de l'utilisateur actuellement connecté
        $projects = Project::where('user_id', Auth::user()->id)->get();

        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *     path="/api/project",
     *     summary="Enregistrer un nouveau projet",
     *     tags={"Projects"},
     *     @OA\Parameter(
     *         name="Accept",
     *         in="header",
     *         @OA\Schema(type="string", example="application/vnd.api+json")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Le nom du projet",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="La description du projet",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="La date de debut du projet",
     *         required=false,
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="La date de fin du projet",
     *         required=false,
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="rate",
     *         in="query",
     *         description="La note du projet",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Photo du projet",
     *         @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(property="image", type="string", format="binary")
     *              )
     *         )
     *     ),
     *     @OA\Response(response="201", description="Projet enregistre avec succes"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function store(StoreProjectRequest $request)
    {
        $request->validated($request->all());

        $file = $request->file('image');

        $name = $file->hashName(); // nom_photo.jpg

        $path = $file->storeAs('images', $name, 'public');

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rate' => $request->rate,
            'user_id' => Auth::user()->id,
            'image' => $path
        ]);

        return $this->successResponse($project, 'Projet créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // if (!Gate::allows('access', $project)) {
        //     return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        // }

        if (Auth::user()->cannot('view', $project)) {
            return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        }

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // if (!Gate::allows('access', $project)) {
        //     return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        // }

        if (Auth::user()->cannot('update', $project)) {
            return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        }

        $request->validated($request->all());

        $project->update($request->all());

        return $this->successResponse($project, 'Projet modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        // if (!Gate::allows('access', $project)) {
        //     return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        // }

        if (Auth::user()->cannot('delete', $project)) {
            return $this->unauthorizedResponse("Vous n'êtes pas autorisé à accéder");
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projet supprimé avec succès'
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $projects = Project::where('name', 'like', value: "%$keyword%")->get();

        return ProjectResource::collection($projects);
    }

}
