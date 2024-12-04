<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Empresa;

class EmpresaController extends Controller
{

    protected $empresas;

    public function __construct(Empresa $empresas)
    {
        $this->empresas = $empresas;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if (!$request->ajax()) {
            return redirect('/');
        }

        try {
            DB::beginTransaction();

            $empresa = Empresa::findOrFail($request->id);
            $empresa->nombre = $request->nombre;
            $empresa->direccion = $request->direccion;
            $empresa->telefono = $request->telefono;
            $empresa->email = $request->email;
            $empresa->nit = $request->nit;
            $empresa->licencia = $request->licencia;

            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($empresa->logo) {
                    Storage::delete('public/logos/' . $empresa->logo);
                }

                // Store new logo
                $logoName = time() . '.' . $request->file('logo')->getClientOriginalExtension();
                $request->file('logo')->storeAs('public/logos', $logoName);
                $empresa->logo = $logoName;
            }

            $empresa->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Empresa actualizada correctamente']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al actualizar la empresa: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        if (!$request->ajax())
            return redirect('/');

        $empresa = Empresa::first();
        
        if ($empresa && $empresa->logo) {
            $empresa->logo = asset('storage/logos/' . $empresa->logo);
        }

        return ['empresa' => $empresa];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function selectEmpresa(Request $request)
    {

        if (!$request->ajax())
            return redirect('/');
        $empresas = Empresa::select('id', 'nombre')->orderBy('nombre', 'asc')->get();
        return ['empresas' => $empresas];
    }
}
