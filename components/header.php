<?php
$userName = $_SESSION['user_name'] ?? 'Utilisateur';
?>

<header>
    <div class="header-content">
        <button id="menu-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Rechercher..." />
        </div>
        <div class="user-info">
            <i class="fas fa-bell"></i>
            <div class="user-profile">
                <div class="jury-avatar president"><?= htmlspecialchars(getInitials($userName)) ?></div>
                <span><?= htmlspecialchars($userName) ?></span>
                <a href="logout.php" style="margin-left: 10px; color: #f00; text-decoration: none; font-weight: bold;">Se déconnecter</a>
            </div>
        </div>
    </div>
</header>
