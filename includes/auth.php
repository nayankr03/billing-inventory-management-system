<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==========================
   LOGIN CHECK
========================== */

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {

        header("Location: ../login.php");
        exit();

    }
}

/* ==========================
   ROLE CHECK
========================== */

function requireRole($roles)
{
    requireLogin();

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    if (!in_array($_SESSION['role'], $roles)) {

        $_SESSION['error'] = "You don't have permission to access this page.";

        header("Location: dashboard.php");
        exit();

    }
}

/* ==========================
   HELPER FUNCTIONS
========================== */

function hasRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function isAdmin()
{
    return hasRole('admin');
}

function isStaff()
{
    return hasRole('staff');
}

/* ==========================
   PAGE ACCESS HELPERS
========================== */

function adminOnly()
{
    requireRole('admin');
}

function staffOnly()
{
    requireRole(['admin', 'staff']);
}