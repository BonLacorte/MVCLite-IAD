<?php
$current = $_SERVER['REQUEST_URI'];
?>

<div class="sidebar" id="sidebar">
    <a class="<?= ($current == '/home') ? 'active' : '' ?>" href="/home">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </a>
    <a class="<?= ($current == '/about') ? 'active' : '' ?>" href="/about">
        <i class="fas fa-info-circle me-2"></i>About
    </a>

    <a class="<?= ($current == '/users') ? 'active' : '' ?>" href="/users">
        <i class="fas fa-user me-2"></i>Users
    </a>

    <a class="<?= ($current == '/settings') ? 'active' : '' ?>" href="/settings">
        <i class="fas fa-cog me-2"></i>Settings
    </a>
</div>