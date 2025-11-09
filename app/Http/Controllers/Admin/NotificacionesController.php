<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;   // 👈 IMPRESCINDIBLE
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $notifs = Notificacion::query()
            ->when($q !== '', fn ($w) => $w->where(function ($qq) use ($q) {
                $qq->where('titulo', 'like', "%{$q}%")
                   ->orWhere('mensaje', 'like', "%{$q}%");
            }))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($n) {
                // Detectar qué columna de “lectura” existe
                $hasLeida    = Schema::hasColumn($n->getTable(), 'leida');
                $hasLeidaAt  = Schema::hasColumn($n->getTable(), 'leida_at');

                return [
                    'id'      => (int) $n->id,
                    'fecha'   => $n->created_at?->format('d/m/Y H:i'),
                    'titulo'  => $n->titulo,
                    'mensaje' => $n->mensaje,
                    'tipo'    => $n->tipo ?? null,
                    // 👇 Enviar flag de lectura a la vista
                    'leida'   => $hasLeida ? (bool) $n->leida
                               : ($hasLeidaAt ? (bool) $n->leida_at : false),
                ];
            });

        return Inertia::render('Admin/Notificaciones/Index', [
            'notificaciones' => $notifs,
            'filters'        => ['q' => $q],
        ]);
    }

    /** PATCH /admin/notificaciones/{n}/leer */
    public function markRead(Notificacion $n)
    {
        $table = $n->getTable();

        if (Schema::hasColumn($table, 'leida')) {
            $n->leida = true;
        } elseif (Schema::hasColumn($table, 'leida_at')) {
            $n->leida_at = now();
        } else {
            return back()->with('info', 'No hay columna de lectura en notificaciones.');
        }

        $n->save();
        return back()->with('success', 'Notificación marcada como leída.');
    }

    /** PATCH /admin/notificaciones/leer-todas */
    public function markAllRead()
    {
        $tmp   = new Notificacion();
        $table = $tmp->getTable();

        if (Schema::hasColumn($table, 'leida')) {
            Notificacion::where('leida', '!=', 1)->update(['leida' => 1]);
        } elseif (Schema::hasColumn($table, 'leida_at')) {
            Notificacion::whereNull('leida_at')->update(['leida_at' => now()]);
        } else {
            return back()->with('info', 'No hay columna de lectura en notificaciones.');
        }

        return back()->with('success', 'Todas marcadas como leídas.');
    }
}
