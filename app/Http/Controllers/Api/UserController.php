<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Admin crée un superviseur
    public function storeSupervisor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'supervisor',
        ]);

        return response()->json($user, 201);
    }

    // Supervisor crée un operator
    public function storeOperator(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'operator',
            'supervisor_id' => $request->user()->id,
        ]);

        return response()->json($user, 201);
    }

    // Lister les utilisateurs selon le rôle
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $users = User::where('role', 'supervisor')->get();
        } elseif ($user->role === 'supervisor') {
            $users = User::where('role', 'operator')
                        ->where('supervisor_id', $user->id)
                        ->get();
        } else {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($users);
    }

    // Supprimer un utilisateur
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}