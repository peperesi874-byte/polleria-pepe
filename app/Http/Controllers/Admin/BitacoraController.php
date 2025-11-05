<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BitacoraLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BitacoraController extends Controller
{
    public function index(Request $request): Response
    {
        $modulo = (string) $request->get('modulo', '');
        $q      = trim((string) $request->get('q', ''));
        $userId = (int) $request->get('user_id', 0);

        $logs = BitacoraLog::query()
            ->when($modulo !== '', fn($w) => $w->where('modulo', $modulo))
            ->when($userId > 0, fn($w) => $w->where('user_id', $userId))
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($q2) use ($q) {
                    $q2->where('accion', 'like', "%{$q}%")
                       ->orWhere('meta', 'like', "%{$q}%");
                });
            })
            ->with('user:id,name')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function ($l) {
                return [
                    'id'    => $l->id,
                    'modulo'=> $l->modulo,
                    'accion'=> $l->accion,
                    'ref_id'=> $l->ref_id,
                    'meta'  => $l->meta ?? [],
                    'user'  => $l->user?->name ?? 'Sistema',
                    'ip'    => $l->ip,
                    'ua'    => $l->ua ? substr($l->ua, 0, 80) . '…' : null,
                    'fecha' => $l->created_at?->format('d/m/Y H:i'),
                ];
            });

        return Inertia::render('Admin/Bitacora/Index', [
            'logs'    => $logs,
            'filters' => ['modulo' => $modulo, 'q' => $q, 'user_id' => $userId],
        ]);
    }
}
