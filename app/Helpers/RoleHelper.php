<?php

namespace App\Helpers;

class RoleHelper
{
    /**
     * Define role hierarchy and permissions
     */
    const ROLES = [
        'user' => 1,
        'admin' => 2,
        'super_admin' => 3,
        'developer' => 4
    ];

    /**
     * Check if a user has permission to access a resource
     *
     * @param string $userRole
     * @param array $allowedRoles
     * @return bool
     */
    public static function hasPermission($userRole, $allowedRoles)
    {
        return in_array($userRole, $allowedRoles);
    }

    /**
     * Check if a user role has higher or equal level than required role
     *
     * @param string $userRole
     * @param string $requiredRole
     * @return bool
     */
    public static function hasRoleLevel($userRole, $requiredRole)
    {
        $userLevel = self::ROLES[$userRole] ?? 0;
        $requiredLevel = self::ROLES[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }

    /**
     * Get all available roles
     *
     * @return array
     */
    public static function getAllRoles()
    {
        return array_keys(self::ROLES);
    }

    /**
     * Get role display name
     *
     * @param string $role
     * @return string
     */
    public static function getRoleDisplayName($role)
    {
        $displayNames = [
            'user' => 'User',
            'admin' => 'Administrator',
            'super_admin' => 'Super Administrator',
            'developer' => 'Developer'
        ];

        return $displayNames[$role] ?? ucfirst($role);
    }
}