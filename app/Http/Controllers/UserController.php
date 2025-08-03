<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Region;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['regions', 'channels', 'classifications'])->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $regions = Region::all();
        $channels = Channel::all();
        return view('users.create', compact('regions', 'channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,manager',
            'region_ids' => 'nullable|array',
            'region_ids.*' => 'exists:regions,id',
            'channel_ids' => 'nullable|array',
            'channel_ids.*' => 'exists:channels,id',
            'classifications' => 'nullable|array',
            'classifications.*' => 'in:food,non_food',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        // Remove region_ids, channel_ids, and classifications from validated data for user creation
        $regionIds = $validated['region_ids'] ?? [];
        $channelIds = $validated['channel_ids'] ?? [];
        $classifications = $validated['classifications'] ?? [];
        unset($validated['region_ids'], $validated['channel_ids'], $validated['classifications']);

        $user = User::create($validated);
        
        // Attach regions, channels, and classifications
        if (!empty($regionIds)) {
            $user->regions()->attach($regionIds);
        }
        if (!empty($channelIds)) {
            $user->channels()->attach($channelIds);
        }
        if (!empty($classifications)) {
            foreach ($classifications as $classification) {
                \App\Models\UserClassification::create([
                    'user_id' => $user->id,
                    'classification' => $classification
                ]);
            }
        }
        
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $regions = Region::all();
        $channels = Channel::all();
        return view('users.edit', compact('user', 'regions', 'channels'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,manager',
            'region_ids' => 'nullable|array',
            'region_ids.*' => 'exists:regions,id',
            'channel_ids' => 'nullable|array',
            'channel_ids.*' => 'exists:channels,id',
            'classifications' => 'nullable|array',
            'classifications.*' => 'in:food,non_food',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Remove region_ids, channel_ids, and classifications from validated data for user update
        $regionIds = $validated['region_ids'] ?? [];
        $channelIds = $validated['channel_ids'] ?? [];
        $classifications = $validated['classifications'] ?? [];
        unset($validated['region_ids'], $validated['channel_ids'], $validated['classifications']);

        $user->update($validated);
        
        // Sync regions, channels, and classifications
        $user->regions()->sync($regionIds);
        $user->channels()->sync($channelIds);
        
        // Update classifications
        $user->classifications()->delete(); // Remove existing classifications
        if (!empty($classifications)) {
            foreach ($classifications as $classification) {
                \App\Models\UserClassification::create([
                    'user_id' => $user->id,
                    'classification' => $classification
                ]);
            }
        }
        
        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
