<?php

namespace App\Http\Controllers;

use App\Models\Hierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class HierarchyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = Hierarchy::with('father', 'mother', 'spouse')->where('created_by', Auth::id())->paginate(10);
        return view('hierarchy.index', ['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hierarchy.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'sex' => 'nullable|in:M,F',
            'dob' => 'nullable|date',
            'dod' => 'nullable|date|gt:dob',
            'avatar' => 'image|mimes:jpg,jpeg,png,gif,bmp,svg,webp',
            'father_id' => 'exists:hierarchies,id',
            'mother_id' => 'exists:hierarchies,id',
            'spouse_id' => 'exists:hierarchies,id',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        if ($request->avatar) {
            $fileName = $request->avatar->getClientOriginalName();
            $path = 'avatars/';
            Storage::disk('public')->putFileAs($path, $request->avatar, $fileName);
            $dbName = 'storage/' . $path . $fileName;
            $data['avatar'] = $dbName;
        }

        Hierarchy::create($data);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Hierarchy $hierarchy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hierarchy $hierarchy)
    {
        return view('hierarchy.edit', ['data' => $hierarchy]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hierarchy $hierarchy)
    {
        $this->validate($request, [
            'name' => 'required',
            'sex' => 'nullable|in:M,F',
            'dob' => 'nullable|date',
            'dod' => 'nullable|date|gt:dob',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif,bmp,svg,webp',
            'father_id' => 'nullable|exists:hierarchies,id',
            'mother_id' => 'nullable|exists:hierarchies,id',
            'spouse_id' => 'nullable|exists:hierarchies,id',
        ]);

        $data = $request->all();

        if ($request->avatar) {
            $fileName = $request->avatar->getClientOriginalName();
            $path = 'avatars/';
            Storage::disk('public')->putFileAs($path, $request->avatar, $fileName);
            $dbName = 'storage/' . $path . $fileName;
            $data['avatar'] = $dbName;
            if ($hierarchy->avatar) {
                Storage::delete($hierarchy->avatar);
            }
        }

        $hierarchy->update($data);

        return redirect()->route('hierarchy.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hierarchy $hierarchy)
    {
        $hierarchy->delete();
        return back();
    }

    public function search(Request $request)
    {
        $data = Hierarchy::where('name', 'like', $request->name . '%')->get(['id', 'name']);

        if (count($data) === 0) {
            return response()->json('Data not found', 404);
        }

        return response()->json($data, 200);
    }

    public function display($id)
    {
        $hierarchy = null;

        // Find the central person by ID or slug
        if (is_numeric($id)) {
            $hierarchy = Hierarchy::find($id);
        }
        if ($hierarchy == null) {
            $hierarchy = Hierarchy::where('slug', $id)->first();
        }

        // If person not found, redirect back with error
        if ($hierarchy == null) {
            Session::flash('error', 'Data not found');
            return back();
        }

        // Initialize arrays to hold graph nodes and links
        $nodes = [];
        $links = [];
        $depthMap = [];
        $addedNodes = [];

        // Helper function to add a person to the nodes array if not already present
        $addPersonToGraph = function ($person, $depth) use (&$nodes, &$addedNodes, &$depthMap) {
            if ($person && !isset($addedNodes[$person->id])) {
                $nodes[] = [
                    'id' => $person->id,
                    'name' => $person->name,
                    'sex' => $person->sex,
                    'dob' => $person->dob ?? null,
                    'dod' => $person->dod ?? null,
                    'avatar' => $person->avatar,
                    'slug' => $person->slug,
                    'depth' => $depth,
                ];
                $addedNodes[$person->id] = true;
                $depthMap[$person->id] = $depth;
            }
        };

        // --- Step 1: Add the central person at depth 0 ---
        $addPersonToGraph($hierarchy, 0);

        // --- Step 2: Traverse upwards (ancestors) ---
        $upQueue = new \SplQueue();
        $upQueue->enqueue(['person' => $hierarchy, 'depth' => 0]);

        $visitedUp = [];

        while (!$upQueue->isEmpty()) {
            $item = $upQueue->dequeue();
            $person = $item['person'];
            $depth = $item['depth'];

            if (isset($visitedUp[$person->id])) continue;
            $visitedUp[$person->id] = true;

            // Process father
            if ($person->father) {
                $addPersonToGraph($person->father, $depth - 1);
                $links[] = ['source' => $person->father->id, 'target' => $person->id, 'type' => 'parent_child'];
                $upQueue->enqueue(['person' => $person->father, 'depth' => $depth - 1]);

                if ($person->father->spouse) {
                    $addPersonToGraph($person->father->spouse, $depth - 1);
                    $links[] = ['source' => $person->father->id, 'target' => $person->father->spouse->id, 'type' => 'spouse'];
                }
            }

            // Process mother
            if ($person->mother) {
                $addPersonToGraph($person->mother, $depth - 1);
                $links[] = ['source' => $person->mother->id, 'target' => $person->id, 'type' => 'parent_child'];
                $upQueue->enqueue(['person' => $person->mother, 'depth' => $depth - 1]);

                if ($person->mother->spouse) {
                    $addPersonToGraph($person->mother->spouse, $depth - 1);
                    $links[] = ['source' => $person->mother->id, 'target' => $person->mother->spouse->id, 'type' => 'spouse'];
                }
            }
        }

        // --- Step 3: Add spouse of the central person ---
        if ($hierarchy->spouse) {
            $addPersonToGraph($hierarchy->spouse, 0);
            $links[] = ['source' => $hierarchy->id, 'target' => $hierarchy->spouse->id, 'type' => 'spouse'];
        }

        // --- Step 4: Traverse downwards (descendants) using a queue for breadth-first traversal ---
        $downQueue = new \SplQueue();
        $downQueue->enqueue(['person' => $hierarchy, 'depth' => 0]);

        $visitedDown = [];

        while (!$downQueue->isEmpty()) {
            $item = $downQueue->dequeue();
            $person = $item['person'];
            $depth = $item['depth'];

            if (isset($visitedDown[$person->id])) continue;
            $visitedDown[$person->id] = true;

            $children = Hierarchy::where('father_id', $person->id)
                ->orWhere('mother_id', $person->id)
                ->get();

            foreach ($children as $child) {
                $addPersonToGraph($child, $depth + 1);
                $links[] = ['source' => $person->id, 'target' => $child->id, 'type' => 'parent_child'];
                $downQueue->enqueue(['person' => $child, 'depth' => $depth + 1]);

                if ($child->spouse) {
                    $addPersonToGraph($child->spouse, $depth + 1);
                    $links[] = ['source' => $child->id, 'target' => $child->spouse->id, 'type' => 'spouse'];
                }
            }
        }

        // --- Step 5: Ensure unique links (can happen if relationships are bidirectional) ---
        $uniqueLinks = [];
        foreach ($links as $link) {
            // Create a canonical key for links to handle (A-B and B-A as same link for spouse)
            if ($link['type'] === 'spouse') {
                // Fix: Assign the array to a variable before sorting
                $pair = [$link['source'], $link['target']];
                sort($pair); // Sorts the array in place
                $key = implode('-', $pair) . '-' . $link['type'];
            } else {
                $key = $link['source'] . '-' . $link['target'] . '-' . $link['type'];
            }
            $uniqueLinks[$key] = $link;
        }
        $links = array_values($uniqueLinks);

        // Pass the structured graph data (nodes and links) to the view
        return view('index', ['graphData' => json_encode(['nodes' => array_values($nodes), 'links' => $links]), 'user' => $hierarchy]);
    }

    public function main(Request $request)
    {
        $query = Hierarchy::query();
        if ($request->filled('name')) {
            $value = $request->name;
            $query->where(function ($q) use ($value) {
                $q->where('name', 'like', $value . '%')
                    ->orWhere('name', 'like', '% ' . $value . '%');
            });
        }

        if ($request->filled('dob')) {
            $query->whereDate('dob', $request->dob);
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->sex);
        }

        if ($request->filled('dod')) {
            $query->where('dod', $request->dod);
        }

        $data['data'] = $query->orderBy('name')->paginate(50);

        if ($request->ajax()) {
            return view('paginated_people', $data)->render();
        }

        return view('welcome', $data);
    }
}
