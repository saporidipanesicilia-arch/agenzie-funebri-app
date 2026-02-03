<?php

use App\Models\Agency;
use App\Models\User;

if (!function_exists('current_agency')) {
    /**
     * Get the current authenticated user's agency
     */
    function current_agency(): ?Agency
    {
        return auth()->check() ? auth()->user()->agency : null;
    }
}

if (!function_exists('current_agency_id')) {
    /**
     * Get the current authenticated user's agency ID
     */
    function current_agency_id(): ?int
    {
        return auth()->check() ? auth()->user()->agency_id : null;
    }
}

if (!function_exists('current_branch_id')) {
    /**
     * Get the current authenticated user's branch ID
     */
    function current_branch_id(): ?int
    {
        return auth()->check() ? auth()->user()->branch_id : null;
    }
}

if (!function_exists('can_access_branch')) {
    /**
     * Check if current user can access a specific branch
     */
    function can_access_branch(int $branchId): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Owner and admin can access all branches
        if ($user->canAccessAllBranches()) {
            return true;
        }

        // Otherwise, check if it's their assigned branch
        return $user->branch_id === $branchId;
    }
}
