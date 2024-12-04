<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Banco;

class BancoController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $buscar = $request->buscar;
        $criterio = $request->criterio;
        $perPage = $request->input('per_page', 5);

        if ($buscar == '') {
            $bancos = Banco::paginate($perPage);
        } else {
            $bancos = Banco::where($criterio, 'like', '%' . $buscar . '%')->paginate($perPage);
        }

        return [
            'pagination' => [
                'total' => $bancos->total(),
                'current_page' => $bancos->currentPage(),
                'per_page' => $bancos->perPage(),
                'last_page' => $bancos->lastPage(),
                'from' => $bancos->firstItem(),
                'to' => $bancos->lastItem(),
            ],
            'bancos' => $bancos
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_cuenta' => 'required',

            'nombre_banco' => 'required',
            'numero_cuenta' => 'required',
            'tipo_cuenta' => 'required',
        ]);

        $banco = Banco::create($request->all());

        return response()->json($banco, 201);
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'nombre_cuenta' => 'required',
            'nombre_banco' => 'required',
            'numero_cuenta' => 'required',
            'tipo_cuenta' => 'required',
        ]);
        $banco = Banco::findOrFail($request->id);

        $banco->update($request->all());

        return response()->json($banco, 200);
    }

    public function getAllData(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        $bancos = Banco::select('nombre_cuenta', 'nombre_banco', 'numero_cuenta')->get();

        return response()->json($bancos, 200);
    }
}
