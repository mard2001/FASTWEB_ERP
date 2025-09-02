<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\RoleHelper;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $users = User::orderBy('created_at', 'desc')->get();
            
            // Transform the data to include formatted dates and status
            $transformedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'user_type' => $user->user_type,
                    'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : null,
                    'email_verified' => $user->email_verified_at ? 'Verified' : 'Not Verified',
                    'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null,
                    'status' => $user->status ?? 1,
                    'status_text' => ($user->status ?? 1) ? 'Active' : 'Deactivated',
                    'deactivation_reason' => $user->deactivation_reason
                ];
            });

            return response()->json([
                'response' => $transformedUsers,
                'status_response' => 1
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users: ' . $e->getMessage());
            return response()->json([
                'response' => 'Error fetching users',
                'status_response' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|string|max:255|unique:users,email',
            'mobile' => 'required|string|min:10|max:15|unique:users,mobile',
            'userType' => 'required|string|in:user,admin,super_admin,developer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $firstField = array_key_first($errors);
            
            return response()->json([
                'response_stat' => 0,
                'field' => $firstField,
                'message' => $errors[$firstField][0],
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'user_type' => $request->userType,
                'password' => Hash::make('admin123') // Default password
            ]);

            if ($user) {
                // Log user creation activity
                activity('user_management')
                    ->causedBy($request->user())
                    ->performedOn($user)
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_by' => $request->user()->name ?? 'System'
                    ])
                    ->log("created a new user account for {$user->name}");

                return response()->json([
                    'response_stat' => 1,
                    'message' => 'User created successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'mobile' => $user->mobile,
                        'user_type' => $user->user_type,
                        'email_verified' => 'Not Verified',
                        'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                        'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null,
                        'status' => 'Pending'
                    ]
                ]);
            }

            return response()->json([
                'response_stat' => 0,
                'message' => 'User creation failed. Please try again.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during user creation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            
            return response()->json([
                'response' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'user_type' => $user->user_type,
                    'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : null,
                    'email_verified' => $user->email_verified_at ? 'Verified' : 'Not Verified',
                    'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null,
                    'status' => $user->email_verified_at ? 'Active' : 'Pending'
                ],
                'status_response' => 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'response' => 'User not found',
                'status_response' => 0
            ], 404);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|string|max:255|unique:users,email,' . $id,
                'mobile' => 'required|string|min:10|max:15|unique:users,mobile,' . $id,
                'userType' => 'required|string|in:user,admin,super_admin,developer',
            ]);

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $firstField = array_key_first($errors);
                
                return response()->json([
                    'response_stat' => 0,
                    'field' => $firstField,
                    'message' => $errors[$firstField][0],
                ], 422);
            }

            $oldData = $user->toArray();
            
            $user->update([
                'name' => $request->firstName . ' ' . $request->lastName,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'user_type' => $request->userType,
            ]);

            // Log user update activity
            activity('user_management')
                ->causedBy($request->user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'updated_by' => $request->user()->name ?? 'System',
                    'old' => $oldData,
                    'attributes' => $user->fresh()->toArray()
                ])
                ->log("updated user account for {$user->name}");

            return response()->json([
                'response_stat' => 1,
                'message' => 'User updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'user_type' => $user->user_type,
                    'email_verified' => $user->email_verified_at ? 'Verified' : 'Not Verified',
                    'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : null,
                    'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null,
                    'status' => $user->email_verified_at ? 'Active' : 'Pending'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during user update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deletion of the current user
            if ($request->user()->id == $id) {
                return response()->json([
                    'response_stat' => 0,
                    'message' => 'You cannot delete your own account.',
                ], 403);
            }

            $userData = $user->toArray();
            
            // Log user deletion activity before deleting
            activity('user_management')
                ->causedBy($request->user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'deleted_by' => $request->user()->name ?? 'System',
                    'deleted_user_data' => $userData
                ])
                ->log("deleted user account for {$user->name}");

            $user->delete();

            return response()->json([
                'response_stat' => 1,
                'message' => 'User deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during user deletion.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics for dashboard.
     */
    public function statistics()
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::whereNotNull('email_verified_at')->count();
            $pendingUsers = User::whereNull('email_verified_at')->count();
            $newThisMonth = User::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();

            return response()->json([
                'response' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'pending_users' => $pendingUsers,
                    'new_this_month' => $newThisMonth
                ],
                'status_response' => 1
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user statistics: ' . $e->getMessage());
            return response()->json([
                'response' => 'Error fetching statistics',
                'status_response' => 0
            ], 500);
        }
    }

    /**
     * Activate a user account.
     */
    public function activate(Request $request, $id)
    {
        try {
            // Check if current user is a developer
            $currentUser = $request->user();
            if (!$currentUser || $currentUser->user_type !== 'developer') {
                return response()->json([
                    'response_stat' => 0,
                    'message' => 'Access denied. Only developers can activate user accounts.'
                ], 403);
            }
            
            $user = User::findOrFail($id);
            
            if ($user->isActive()) {
                return response()->json([
                    'response_stat' => 0,
                    'message' => 'User is already active'
                ], 400);
            }

            $user->activate();

            // Log activation activity
            activity('user_management')
                ->causedBy($request->user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'activated_by' => $request->user()->name ?? 'System'
                ])
                ->log("activated user account for {$user->name}");

            return response()->json([
                'response_stat' => 1,
                'message' => 'User activated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->status,
                    'status_text' => 'Active',
                    'deactivation_reason' => null
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error activating user: ' . $e->getMessage());
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during user activation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a user account.
     */
    public function deactivate(Request $request, $id)
    {
        // Check if current user is a developer
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->user_type !== 'developer') {
            return response()->json([
                'response_stat' => 0,
                'message' => 'Access denied. Only developers can deactivate user accounts.'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'response_stat' => 0,
                'message' => 'Deactivation reason is required',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);
            
            if (!$user->isActive()) {
                return response()->json([
                    'response_stat' => 0,
                    'message' => 'User is already deactivated'
                ], 400);
            }

            // Prevent self-deactivation
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'response_stat' => 0,
                    'message' => 'You cannot deactivate your own account'
                ], 400);
            }

            $user->deactivate($request->reason);

            // Log deactivation activity
            activity('user_management')
                ->causedBy($request->user())
                ->performedOn($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'deactivated_by' => $request->user()->name ?? 'System',
                    'reason' => $request->reason
                ])
                ->log("deactivated user account for {$user->name}");

            return response()->json([
                'response_stat' => 1,
                'message' => 'User deactivated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->status,
                    'status_text' => 'Deactivated',
                    'deactivation_reason' => $user->deactivation_reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error deactivating user: ' . $e->getMessage());
            return response()->json([
                'response_stat' => 0,
                'message' => 'An error occurred during user deactivation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}