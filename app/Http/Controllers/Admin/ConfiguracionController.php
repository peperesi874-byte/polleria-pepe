<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\BitacoraService; // ✅ Para registrar en bitácora

class ConfiguracionController extends Controller
{
    /** GET /admin/configuracion */
    public function edit()
    {
        // Crea (si no existe) la fila única (id = 1)
        $cfg = Configuracion::firstOrCreate(
            ['id' => 1],
            [
                'horario_apertura'  => '09:00',
                'horario_cierre'    => '18:00',
                'zona_cobertura'    => 'Tapachula',
                'umbral_stock_bajo' => 5,
            ]
        );

        return Inertia::render('Admin/Configuracion/Edit', [
            'cfg' => [
                'horario_apertura' => $cfg->horario_apertura,
                'horario_cierre'   => $cfg->horario_cierre,
                'zona_cobertura'   => $cfg->zona_cobertura,
                // La vista usa "stock_umbral", pero la columna real es "umbral_stock_bajo"
                'stock_umbral'     => (int) ($cfg->umbral_stock_bajo ?? 0),
            ],
        ]);
    }

    /** PUT /admin/configuracion */
    public function update(Request $request)
    {
        // ✅ Validación de datos desde el formulario Vue
        $data = $request->validate([
            'horario_apertura' => ['required', 'string'],
            'horario_cierre'   => ['required', 'string'],
            'zona_cobertura'   => ['required', 'string', 'max:100'],
            'stock_umbral'     => ['required', 'integer', 'min:0'],
        ]);

        // Normalizamos formato a HH:MM (24h)
        $apertura = date('H:i', strtotime($data['horario_apertura']));
        $cierre   = date('H:i', strtotime($data['horario_cierre']));

        // Obtenemos o creamos el registro único
        $cfg = Configuracion::firstOrCreate(['id' => 1]);

        // Mapeo a columnas reales de la tabla
        $toSave = [
            'horario_apertura'  => $apertura,
            'horario_cierre'    => $cierre,
            'zona_cobertura'    => $data['zona_cobertura'],
            'umbral_stock_bajo' => (int) $data['stock_umbral'],
        ];

        // Guardamos cambios
        $cfg->update($toSave);

        // 📝 Registro en bitácora
        BitacoraService::add(
            'Configuración', // Módulo
            'Actualización', // Acción
            null,
            [
                'detalle' => 'El administrador actualizó los parámetros de configuración del sistema (horarios, zona de cobertura y umbral de stock).',
                'cambios' => $toSave,
            ]
        );

        // Redirigimos con mensaje flash
        return back()->with('success', 'Configuración guardada correctamente.');
    }
}
