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
        $addedNodes = []; // Keep track of nodes already added to prevent duplicates

        // Define a maximum depth for fetching relatives to avoid overly large graphs
        // Adjust these values as needed for your desired graph size.
        $maxGenerationsUp = 3;   // How many generations up from the central person
        $maxGenerationsDown = 2; // How many generations down from the central person (including self as depth 0)

        // Helper function to add a person to the nodes array if not already present
        $addPersonToGraph = function ($person) use (&$nodes, &$addedNodes) {
            if ($person && !isset($addedNodes[$person->id])) {
                $nodes[] = [
                    'id' => $person->id,
                    'name' => $person->name,
                    'sex' => $person->sex,
                    'dob' => $person->dob ? $person->dob->toDateString() : null,
                    'dod' => $person->dod ? $person->dod->toDateString() : null,
                    'avatar' => $person->avatar,
                    'slug' => $person->slug,
                ];
                $addedNodes[$person->id] = true;
            }
        };

        // --- Step 1: Add the central person ---
        $addPersonToGraph($hierarchy);

        // --- Step 2: Traverse upwards (ancestors) ---
        $currentPersonForUp = $hierarchy;
        for ($i = 0; $i < $maxGenerationsUp; $i++) {
            $hasNewParents = false;

            // Add father and link
            if ($currentPersonForUp->father) {
                $addPersonToGraph($currentPersonForUp->father);
                $links[] = ['source' => $currentPersonForUp->father->id, 'target' => $currentPersonForUp->id, 'type' => 'parent_child'];
                // Add father's spouse (mother of current person) and link
                if ($currentPersonForUp->father->spouse) {
                    $addPersonToGraph($currentPersonForUp->father->spouse);
                    $links[] = ['source' => $currentPersonForUp->father->id, 'target' => $currentPersonForUp->father->spouse->id, 'type' => 'spouse'];
                }
                $hasNewParents = true;
            }

            // Add mother and link
            if ($currentPersonForUp->mother) {
                $addPersonToGraph($currentPersonForUp->mother);
                $links[] = ['source' => $currentPersonForUp->mother->id, 'target' => $currentPersonForUp->id, 'type' => 'parent_child'];
                // Add mother's spouse (father of current person) and link
                if ($currentPersonForUp->mother->spouse && !isset($addedNodes[$currentPersonForUp->mother->spouse->id])) {
                    $addPersonToGraph($currentPersonForUp->mother->spouse);
                    $links[] = ['source' => $currentPersonForUp->mother->id, 'target' => $currentPersonForUp->mother->spouse->id, 'type' => 'spouse'];
                }
                $hasNewParents = true;
            }

            // Move up to the next generation of parents (if any of the current parents exist)
            if ($currentPersonForUp->father) {
                $currentPersonForUp = $currentPersonForUp->father; // Arbitrarily pick father to continue ascent
            } elseif ($currentPersonForUp->mother) {
                $currentPersonForUp = $currentPersonForUp->mother; // If no father, try mother
            } else {
                break; // No more parents to trace
            }

            if (!$hasNewParents) {
                break; // Stop if no new parents were found in this generation
            }
        }

        // --- Step 3: Add spouse of the central person ---
        if ($hierarchy->spouse) {
            $addPersonToGraph($hierarchy->spouse);
            $links[] = ['source' => $hierarchy->id, 'target' => $hierarchy->spouse->id, 'type' => 'spouse'];
        }

        // --- Step 4: Traverse downwards (descendants) using a queue for breadth-first traversal ---
        $queue = new \SplQueue();
        $queue->enqueue(['person' => $hierarchy, 'depth' => 0]); // Start with the central person at depth 0

        $processedForChildren = []; // To avoid processing the same person's children multiple times

        while (!$queue->isEmpty()) {
            $item = $queue->dequeue();
            $currentPerson = $item['person'];
            $currentDepth = $item['depth'];

            // Skip if already processed for children or if max depth reached
            if (isset($processedForChildren[$currentPerson->id]) || $currentDepth > $maxGenerationsDown) {
                continue;
            }
            $processedForChildren[$currentPerson->id] = true;

            // Fetch children of the current person
            $children = Hierarchy::where('father_id', $currentPerson->id)
                ->orWhere('mother_id', $currentPerson->id)
                ->get();

            foreach ($children as $child) {
                $addPersonToGraph($child);
                $links[] = ['source' => $currentPerson->id, 'target' => $child->id, 'type' => 'parent_child'];
                $queue->enqueue(['person' => $child, 'depth' => $currentDepth + 1]);

                // Also add the child's spouse if they have one and it's within depth
                if ($child->spouse && $currentDepth + 1 <= $maxGenerationsDown) {
                    $addPersonToGraph($child->spouse);
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
        return view('index', ['graphData' => json_encode(['nodes' => array_values($nodes), 'links' => $links])]);
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
