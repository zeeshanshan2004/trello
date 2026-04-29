<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'father_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'workspace_id' => 'nullable|exists:workspaces,id',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('clients', 'public');
        }

        Client::create([
            'workspace_id' => $request->workspace_id,
            'name' => $request->name,
            'email' => $request->email,
            'father_name' => $request->father_name,
            'phone' => $request->phone,
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Client added successfully.');
    }

    public function show(Client $client)
    {
        // Boards explicitly assigned to this client
        $explicitBoardIds = $client->boards()->pluck('id')->toArray();
        
        // Boards that have cards assigned to this client
        $cardBoardIds = \App\Models\Card::where('client_id', $client->id)
            ->join('lists', 'cards.list_id', '=', 'lists.id')
            ->pluck('lists.board_id')
            ->unique()
            ->toArray();
            
        $allBoardIds = array_unique(array_merge($explicitBoardIds, $cardBoardIds));
        
        $boards = \App\Models\Board::with('labels')->whereIn('id', $allBoardIds)->where('is_archived', false)->get();
        
        // Cards assigned to this client
        $cards = \App\Models\Card::with(['list.board', 'comments', 'attachments', 'checklistItems', 'members'])
            ->where('client_id', $client->id)
            ->where('is_archived', false)
            ->get();
        
        return view('clients.show', compact('client', 'boards', 'cards'));
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        if ($client->image_path) {
            Storage::disk('public')->delete($client->image_path);
        }
        $client->delete();
        return back()->with('success', 'Client deleted successfully.');
    }
}
