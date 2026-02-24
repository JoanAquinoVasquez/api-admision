<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        //Retornar usuarios con roles sin timestamps y solo valores que quiero mostrar
        $users = User::with('roles:id,nombre')->get(['id', 'name', 'email', 'estado', 'created_at']);
        return response()->json($users);
    }

    // Mostrar un usuario
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }

    // Crear un nuevo usuario
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name'  => 'nullable|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users',
                    'regex:/^[A-Za-z0-9._%+-]+@unprg\.edu\.pe$/i'
                ],
                'rol'   => 'nullable|string|max:50|exists:roles,nombre',
            ],
            [
                'email.regex' => 'El correo electrónico debe pertenecer al dominio de la UNPRG (@unprg.edu.pe)',
                'rol.exists'  => 'El rol especificado no existe en el sistema',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Crear usuario
            $user = User::create([
                'name'  => $request->name,
                'email' => $request->email,
            ]);

            // Asignar rol si se proporcionó
            if ($request->filled('rol')) {
                $role = Role::where('nombre', $request->rol)->first();
                $user->roles()->attach($role->id);
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario creado exitosamente',
                'data'    => $user->load('roles'), // cargar roles asignados
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error al crear el usuario',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|string|email|max:255|regex:/^[A-Za-z0-9._%+-]+@unprg\.edu\.pe$/i|unique:users,email,' . $user->id,
            'estado' => 'sometimes|boolean',
            'rol'    => 'sometimes|string|max:50|exists:roles,nombre',
        ], [
            'email.regex' => 'El correo electrónico debe pertenecer al dominio de la UNPRG (@unprg.edu.pe)',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $validated = $validator->validated();

            // Actualizar datos básicos del usuario (sin rol todavía)
            $user->update(Arr::except($validated, ['rol']));

            // Si envían el campo rol → actualizar relación
            if ($request->filled('rol')) {
                $role = Role::where('nombre', $request->rol)->first();
                if ($role) {
                    $user->roles()->sync([$role->id]); // Reemplaza el rol actual por el nuevo
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Usuario actualizado exitosamente',
                'data'    => $user->load('roles'), // Retornar usuario con roles actualizados
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al actualizar el usuario'], 500);
        }
    }
}
