<?php

require_once "../includes/auth.php";

$currentPage = basename($_SERVER['PHP_SELF']);

?>

<div class="sidebar">

    <div class="logo">

        <h3 class="mb-1">BIMS</h3>

    </div>



    <ul>

        <!-- Dashboard -->
        <li>
            <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>

        <?php if (isAdmin()): ?>

            <!-- Products -->
            <li>
                <a href="products.php" class="<?= $currentPage == 'products.php' ? 'active' : ''; ?>">
                    <i class="bi bi-box-seam"></i>
                    Products
                </a>
            </li>

        <?php endif; ?>

        <!-- Customers -->
        <li>
            <a href="customer.php" class="<?= $currentPage == 'customer.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i>
                Customers
            </a>
        </li>

        <!-- Sales -->
        <li>
            <a href="sales.php"
                class="<?= in_array($currentPage, ['sales.php', 'new_sale.php', 'invoice.php']) ? 'active' : ''; ?>">
                <i class="bi bi-receipt-cutoff"></i>
                Sales
            </a>
        </li>

        <?php if (isAdmin()): ?>

            <!-- Reports -->
            <li>
                <a href="reports.php" class="<?= $currentPage == 'reports.php' ? 'active' : ''; ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                    Reports
                </a>
            </li>

            <!-- Settings -->
            <li>
                <a href="settings.php" class="<?= $currentPage == 'settings.php' ? 'active' : ''; ?>">
                    <i class="bi bi-gear-fill"></i>
                    Settings
                </a>
            </li>

            <!-- Users -->
            <li>
                <a href="users.php" class="<?= in_array($currentPage, ['users.php', 'add_user.php', 'edit_user.php']) ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i>
                    Users
                </a>
            </li>

        <?php endif; ?>

        <li>
            <a href="profile.php" class="<?= $currentPage == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i>
                My Profile
            </a>
        </li>


        <li>
            <a href="../logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>
</div>