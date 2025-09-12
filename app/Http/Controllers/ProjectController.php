<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Traits\ApiResponseTrait;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of the resource.
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

        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $request->validated($request->all());

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rate' => $request->rate,
            'user_id' => Auth::user()->id
        ]);

        return $this->successResponse($project, 'Projet créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $request->validated($request->all());

        $project->update($request->all());

        return $this->successResponse($project, 'Projet modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
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

        return response()->json($projects);
    }

}
