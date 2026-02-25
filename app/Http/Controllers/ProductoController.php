<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    private function soloAdmin()
    {
        if (strtolower(trim(auth()->user()->rol)) !== 'administrador') {
            abort(403);
        }
    }

    // LISTAR
    public function index(Request $request)
    {
        $this->soloAdmin();

        $q = $request->get('q');

        // Traer productos con su categoría
        $productos = Producto::with('categoria')
            ->when($q, function($qq) use ($q){
                $qq->where('nombre', 'like', "%{$q}%");
            })
            ->orderByDesc('id_producto')
            ->get();

        // Traer categorías para los selects
        $categorias = Categoria::orderBy('nombre')->get();

        // Separar por categoría (IDs: 1 Panadería, 2 Pasteles, 3 Roscas)
        $panaderia = $productos->where('id_categoria', 1);
        $pasteles  = $productos->where('id_categoria', 2);
        $roscas    = $productos->where('id_categoria', 3);

        return view('admin.products', compact(
            'q',
            'categorias',
            'panaderia',
            'pasteles',
            'roscas',        
        ));
    }

    // CREAR
    public function store(Request $request)
    {
        $this->soloAdmin();

        $data = $request->validate([
            'nombre'       => 'required|string|max:120',
            'precio_venta' => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'id_categoria' => 'required|exists:categorias,id_categoria',

            // extras por categoría
            'tipo_pan'   => 'nullable|in:Dulce,Salado',
            'tam_pastel' => 'nullable|in:Chico,Mediano,Grande',
            'sabor'      => 'nullable|string|max:60',
            'tam_rosca'  => 'nullable|in:Chica,Mediana,Grande',
            'relleno'    => 'nullable|string|max:80',
        ]);

        Producto::create($data);

        return redirect()->route('products')->with('ok', 'Producto agregado.');
    }

    // EDITAR
    public function update(Request $request, $id)
    {
        $this->soloAdmin();

        $producto = Producto::findOrFail($id);

        $data = $request->validate([
            'nombre'       => 'required|string|max:120',
            'precio_venta' => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'id_categoria' => 'required|exists:categorias,id_categoria',

            // extras por categoría
            'tipo_pan'   => 'nullable|in:Dulce,Salado',
            'tam_pastel' => 'nullable|in:Chico,Mediano,Grande',
            'sabor'      => 'nullable|string|max:60',
            'tam_rosca'  => 'nullable|in:Chica,Mediana,Grande',
            'relleno'    => 'nullable|string|max:80',
        ]);

        // 🔥 Limpieza opcional (recomendado):
        // Si cambiaste a "Pasteles", borra campos de otras categorías para no guardar basura
        $cat = (int) $data['id_categoria'];

        if ($cat === 1) { // Panadería
            $data['tam_pastel'] = null;
            $data['sabor']      = null;
            $data['tam_rosca']  = null;
            $data['relleno']    = null;
        } elseif ($cat === 2) { // Pasteles
            $data['tipo_pan']   = null;
            $data['tam_rosca']  = null;
            $data['relleno']    = null;
        } elseif ($cat === 3) { // Roscas
            $data['tipo_pan']   = null;
            $data['tam_pastel'] = null;
            $data['sabor']      = null;
        } else { // Otros
            $data['tipo_pan']   = null;
            $data['tam_pastel'] = null;
            $data['sabor']      = null;
            $data['tam_rosca']  = null;
            $data['relleno']    = null;
        }

        $producto->update($data);

        return redirect()->route('products')->with('ok', 'Producto actualizado.');
    }

    // ELIMINAR
    public function destroy($id)
    {
        $this->soloAdmin();

        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('products')->with('ok', 'Producto eliminado.');
    }
}