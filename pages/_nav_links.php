<?php
// Barre de navigation globale réutilisable sur toutes les pages
$currentPage = basename($_SERVER['PHP_SELF']);

// Détecter si on est dans la racine ou dans le dossier pages/
$isInRoot = dirname($_SERVER['PHP_SELF']) === '/app_go' || dirname($_SERVER['PHP_SELF']) === '';
$prefix = $isInRoot ? 'pages/' : '';

$links = [
    ['href' => $isInRoot ? 'index.php' : '../index.php', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Tableau de bord'],
    ['href' => $prefix . 'clients.php', 'icon' => 'fas fa-users', 'label' => 'Clients'],
    ['href' => $prefix . 'admin_add_client.php', 'icon' => 'fas fa-user-plus', 'label' => 'Ajouter client'],
    ['href' => $prefix . 'paiements.php', 'icon' => 'fas fa-history', 'label' => 'Paiements'],
    ['href' => $prefix . 'ajouter_paiement.php', 'icon' => 'fas fa-money-bill-wave', 'label' => 'Ajouter paiement'],
    ['href' => $prefix . 'abonnements.php', 'icon' => 'fas fa-id-card', 'label' => 'Abonnements'],
    ['href' => $prefix . 'ajouter_abonnement.php', 'icon' => 'fas fa-plus-circle', 'label' => 'Ajouter abonnement'],
    ['href' => $prefix . 'admin_presences.php', 'icon' => 'fas fa-clipboard-check', 'label' => 'Présences'],
];
?>

<style>
.global-nav {
  background: var(--dark-gray, #1a1a1a);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  position: sticky;
  top: 0;
  z-index: 980;
  box-shadow: 0 2px 12px rgba(0,0,0,0.25);
}
.global-nav .container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 8px 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.global-nav a {
  color: var(--white, #fff);
  text-decoration: none;
  background: var(--light-gray, #2a2a2a);
  border: 1px solid rgba(255,255,255,0.08);
  padding: 8px 12px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: var(--transition);
  font-size: 0.95rem;
}
.global-nav a:hover { background: #333; }
.global-nav a.active { background: var(--orange, #f15a24); border-color: transparent; color: #111; }
.global-nav a i { opacity: 0.9; }
@media (max-width: 576px) {
  .global-nav .container { padding: 6px 12px; gap: 8px; }
  .global-nav a { padding: 7px 10px; font-size: 0.9rem; }
}

/* Masquer la nav globale sur mobile (on laisse le menu hamburger) */
@media (max-width: 768px) {
  .global-nav { display: none !important; }
}
</style>

<nav class="global-nav">
  <div class="container">
    <?php foreach ($links as $link): ?>
      <?php $active = $currentPage === $link['href'] ? 'active' : ''; ?>
      <a href="<?= htmlspecialchars($link['href']) ?>" class="<?= $active ?>">
        <i class="<?= htmlspecialchars($link['icon']) ?>"></i>
        <span><?= htmlspecialchars($link['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  </nav>


