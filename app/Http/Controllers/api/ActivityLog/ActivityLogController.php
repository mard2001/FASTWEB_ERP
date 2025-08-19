<?php

namespace App\Http\Controllers\api\ActivityLog;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::with(['causer', 'subject'])
                ->orderBy('created_at', 'desc');

            // Apply filters if provided
            if ($request->has('activity_type') && $request->activity_type) {
                $query->where('event', $request->activity_type);
            }

            if ($request->has('subject_type') && $request->subject_type) {
                $query->where('subject_type', $request->subject_type);
            }

            if ($request->has('user_name') && $request->user_name) {
                $query->whereHas('causer', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->user_name . '%')
                      ->orWhere('email', 'like', '%' . $request->user_name . '%');
                });
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $activityLogs = $query->get();

            // Format the data for DataTables
            $formattedLogs = $activityLogs->map(function ($log) {
                // Ensure properties is properly formatted
                $properties = $log->properties;
                if (is_string($properties)) {
                    $properties = json_decode($properties, true) ?? [];
                }
                if (!is_array($properties)) {
                    $properties = [];
                }

                return [
                    'id' => $log->id,
                    'log_name' => $log->log_name,
                    'description' => $log->description,
                    'event' => $log->event,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'causer_name' => $log->causer ? $log->causer->name : 'System',
                    'properties' => $properties,
                    'created_at' => $log->created_at->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Activity logs retrieved successfully',
                'data' => $formattedLogs,
            ], 200);

        } catch (\Exception $e) {
            // Handle case where table doesn't exist yet
            if (str_contains($e->getMessage(), 'tblActivityLog') && 
                (str_contains($e->getMessage(), 'does not exist') || str_contains($e->getMessage(), 'doesn\'t exist'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity log table not found. Please execute the SQL script to create the table.',
                    'error' => 'TABLE_NOT_FOUND',
                    'data' => [],
                    'statistics' => ['total' => 0, 'today' => 0, 'unique_users' => 0, 'total_logins' => 0]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving activity logs: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Display the specified activity log.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $activityLog = ActivityLog::with(['causer', 'subject'])->findOrFail($id);

            // Ensure properties is properly formatted
            $properties = $activityLog->properties;
            if (is_string($properties)) {
                $properties = json_decode($properties, true) ?? [];
            }
            if (!is_array($properties)) {
                $properties = [];
            }

            $formattedLog = [
                'id' => $activityLog->id,
                'log_name' => $activityLog->log_name,
                'description' => $activityLog->description,
                'event' => $activityLog->event,
                'subject_type' => $activityLog->subject_type,
                'subject_id' => $activityLog->subject_id,
                'subject_data' => $activityLog->subject,
                'causer_name' => $activityLog->causer ? $activityLog->causer->name : 'System',
                'user_name' => $activityLog->causer ? $activityLog->causer->name : 'System',
                'causer_type' => $activityLog->causer_type,
                'causer_data' => $activityLog->causer,
                'properties' => $properties,
                'batch_uuid' => $activityLog->batch_uuid,
                'created_at' => $activityLog->created_at->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'updated_at' => $activityLog->updated_at->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Activity log retrieved successfully',
                'data' => $formattedLog
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving activity log: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get activity log statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics(Request $request)
    {
        try {
            // Check if tblActivityLog table exists
            $tableExists = DB::select("SELECT name FROM sysobjects WHERE name='tblActivityLog' AND xtype='U'");
            
            if (empty($tableExists)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity log table not found. Please execute the SQL script first.',
                    'error' => 'TABLE_NOT_FOUND',
                    'data' => [
                        'total_activities' => 0,
                        'today_activities' => 0,
                        'unique_users' => 0,
                        'total_logins' => 0,
                        'activities_by_event' => [],
                        'top_users' => []
                    ]
                ]);
            }

            $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
            $dateTo = $request->get('date_to', now()->format('Y-m-d'));

            // Get basic statistics
            $totalActivities = ActivityLog::whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->count();

            $todayActivities = ActivityLog::whereDate('created_at', now()->format('Y-m-d'))->count();

            $uniqueUsers = ActivityLog::whereNotNull('causer_id')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->distinct()
                ->count('causer_id');

            $totalLogins = ActivityLog::where('event', 'login')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->count();

            $stats = ActivityLog::selectRaw('
                    event,
                    COUNT(*) as count,
                    CAST(created_at as DATE) as date
                ')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->groupBy('event', DB::raw('CAST(created_at as DATE)'))
                ->orderBy('date', 'desc')
                ->get()
                ->groupBy('event');

            $topUsers = ActivityLog::selectRaw('
                    causer_id,
                    causer_type,
                    COUNT(*) as activity_count
                ')
                ->whereNotNull('causer_id')
                ->whereDate('created_at', '>=', $dateFrom)
                ->whereDate('created_at', '<=', $dateTo)
                ->groupBy('causer_id', 'causer_type')
                ->orderBy('activity_count', 'desc')
                ->limit(10)
                ->with('causer')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_name' => $item->causer ? ($item->causer->name ?? $item->causer->email) : 'Unknown',
                        'activity_count' => $item->activity_count
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Activity log statistics retrieved successfully',
                'data' => [
                    'total_activities' => $totalActivities,
                    'today_activities' => $todayActivities,
                    'unique_users' => $uniqueUsers,
                    'total_logins' => $totalLogins,
                    'activities_by_event' => $stats,
                    'top_users' => $topUsers,
                    'date_range' => [
                        'from' => $dateFrom,
                        'to' => $dateTo
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            // Handle case where table doesn't exist yet
            if (str_contains($e->getMessage(), 'tblActivityLog') && 
                (str_contains($e->getMessage(), 'Invalid object name') || 
                 str_contains($e->getMessage(), 'does not exist') || 
                 str_contains($e->getMessage(), 'doesn\'t exist'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Activity log table not found. Please execute the SQL script to create the table.',
                    'error' => 'TABLE_NOT_FOUND',
                    'data' => [
                        'total_activities' => 0,
                        'today_activities' => 0,
                        'unique_users' => 0,
                        'total_logins' => 0,
                        'activities_by_event' => [],
                        'top_users' => []
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage(),
                'data' => [
                    'total_activities' => 0,
                    'today_activities' => 0,
                    'unique_users' => 0,
                    'total_logins' => 0,
                    'activities_by_event' => [],
                    'top_users' => []
                ]
            ], 500);
        }
    }

    /**
     * Get available log names for filtering.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogNames()
    {
        try {
            $logNames = ActivityLog::distinct()->pluck('log_name')->filter()->values();

            return response()->json([
                'success' => true,
                'message' => 'Log names retrieved successfully',
                'data' => $logNames
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving log names: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get available events for filtering.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents()
    {
        try {
            $events = ActivityLog::distinct()->pluck('event')->filter()->values();

            return response()->json([
                'success' => true,
                'message' => 'Events retrieved successfully',
                'data' => $events
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving events: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Delete old activity logs (cleanup).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cleanup(Request $request)
    {
        try {
            $request->validate([
                'days' => 'required|integer|min:1|max:365'
            ]);

            $cutoffDate = now()->subDays($request->days);
            $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} activity log records older than {$request->days} days",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cleaning up activity logs: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Test IP address capture for activity logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testIpCapture(Request $request)
    {
        try {
            // Create a test activity log entry
            activity('test')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'test_data' => 'This is a test entry to verify IP capture'
                ])
                ->log('IP capture test performed');

            // Get the latest activity log to verify the data
            $latestLog = ActivityLog::orderBy('created_at', 'desc')->first();
            
            return response()->json([
                'success' => true,
                'message' => 'Test activity logged successfully',
                'data' => [
                    'captured_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'latest_log' => [
                        'id' => $latestLog->id,
                        'description' => $latestLog->description,
                        'properties' => $latestLog->properties,
                        'created_at' => $latestLog->created_at->setTimezone('Asia/Manila')->format('Y-m-d H:i:s')
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing IP capture: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
