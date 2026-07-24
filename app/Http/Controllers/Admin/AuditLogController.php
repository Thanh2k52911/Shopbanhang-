<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Danh sách nhật ký quản trị.
     */
    public function index(Request $request): View
    {
        $query = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->select([
                'al.id',
                'al.user_id',
                'al.action',
                'al.auditable_type',
                'al.auditable_id',
                'al.description',
                'al.old_values',
                'al.new_values',
                'al.route_name',
                'al.url',
                'al.request_method',
                'al.ip_address',
                'al.user_agent',
                'al.created_at',
                'al.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
                'u.status as user_status',
            ]);

        $keyword = trim((string) $request->input('keyword'));

        if ($keyword !== '') {
            $query->where(
                function (Builder $builder) use ($keyword): void {
                    $builder
                        ->where(
                            'u.name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'u.email',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.action',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.description',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.auditable_type',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.route_name',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.url',
                            'like',
                            '%' . $keyword . '%'
                        )
                        ->orWhere(
                            'al.ip_address',
                            'like',
                            '%' . $keyword . '%'
                        );
                }
            );
        }

        if ($request->filled('action')) {
            $query->where(
                'al.action',
                $request->input('action')
            );
        }

        if ($request->filled('user_id')) {
            $query->where(
                'al.user_id',
                (int) $request->input('user_id')
            );
        }

        if ($request->filled('request_method')) {
            $query->where(
                'al.request_method',
                strtoupper(
                    (string) $request->input('request_method')
                )
            );
        }

        if ($request->filled('auditable_type')) {
            $query->where(
                'al.auditable_type',
                $request->input('auditable_type')
            );
        }

        if ($request->filled('date_from')) {
            $query->whereDate(
                'al.created_at',
                '>=',
                $request->input('date_from')
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'al.created_at',
                '<=',
                $request->input('date_to')
            );
        }

        match ($request->input('sort')) {
            'oldest' => $query->orderBy('al.created_at'),
            'action' => $query
                ->orderBy('al.action')
                ->orderByDesc('al.created_at'),
            'user' => $query
                ->orderBy('u.name')
                ->orderByDesc('al.created_at'),
            default => $query->orderByDesc('al.created_at'),
        };

        $logs = $query
            ->paginate(30)
            ->withQueryString();

        $statistics = DB::table('audit_logs')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'created' THEN 1 ELSE 0 END),
                    0
                ) as created_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'updated' THEN 1 ELSE 0 END),
                    0
                ) as updated_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'deleted' THEN 1 ELSE 0 END),
                    0
                ) as deleted_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'login' THEN 1 ELSE 0 END),
                    0
                ) as login_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'approved' THEN 1 ELSE 0 END),
                    0
                ) as approved_count"
            )
            ->selectRaw(
                "COALESCE(
                    SUM(CASE WHEN action = 'rejected' THEN 1 ELSE 0 END),
                    0
                ) as rejected_count"
            )
            ->first();

        $actions = DB::table('audit_logs')
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $auditableTypes = DB::table('audit_logs')
            ->whereNotNull('auditable_type')
            ->where('auditable_type', '!=', '')
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        $users = DB::table('users as u')
            ->whereNull('u.deleted_at')
            ->whereExists(
                function (Builder $builder): void {
                    $builder
                        ->selectRaw('1')
                        ->from('audit_logs as al')
                        ->whereColumn(
                            'al.user_id',
                            'u.id'
                        );
                }
            )
            ->orderBy('u.name')
            ->get([
                'u.id',
                'u.name',
                'u.email',
            ]);

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'statistics' => $statistics,
            'actions' => $actions,
            'auditableTypes' => $auditableTypes,
            'users' => $users,
        ]);
    }

    /**
     * Chi tiết một nhật ký.
     */
    public function show(int $auditLog): View
    {
        $log = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->where('al.id', $auditLog)
            ->select([
                'al.id',
                'al.user_id',
                'al.action',
                'al.auditable_type',
                'al.auditable_id',
                'al.description',
                'al.old_values',
                'al.new_values',
                'al.route_name',
                'al.url',
                'al.request_method',
                'al.ip_address',
                'al.user_agent',
                'al.created_at',
                'al.updated_at',

                'u.name as user_name',
                'u.email as user_email',
                'u.avatar as user_avatar',
                'u.status as user_status',
                'u.last_login_at',
                'u.last_login_ip',
            ])
            ->first();

        abort_if(! $log, 404);

        $log->old_values_array = $this->decodeJson(
            $log->old_values
        );

        $log->new_values_array = $this->decodeJson(
            $log->new_values
        );

        $relatedLogs = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->where('al.id', '!=', $log->id)
            ->when(
                $log->auditable_type,
                fn (Builder $query): Builder =>
                    $query->where(
                        'al.auditable_type',
                        $log->auditable_type
                    )
            )
            ->when(
                $log->auditable_id,
                fn (Builder $query): Builder =>
                    $query->where(
                        'al.auditable_id',
                        $log->auditable_id
                    )
            )
            ->orderByDesc('al.created_at')
            ->limit(10)
            ->get([
                'al.id',
                'al.user_id',
                'al.action',
                'al.description',
                'al.created_at',
                'u.name as user_name',
                'u.email as user_email',
            ]);

        return view('admin.audit-logs.show', [
            'log' => $log,
            'relatedLogs' => $relatedLogs,
        ]);
    }

    /**
     * Decode JSON an toàn.
     *
     * @return array<string, mixed>
     */
    private function decodeJson(
        mixed $value
    ): array {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode(
            (string) $value,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }
}
