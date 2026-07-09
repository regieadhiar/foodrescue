<?php
// index.php

require_once __DIR__ . '/includes/auth.php';

// 0. Landing page check — show once per session
if (!isset($_SESSION['visited']) && !isset($_GET['skip'])) {
    $_SESSION['visited'] = true;
    include __DIR__ . '/landing.php';
    exit;
}
$_SESSION['visited'] = true;

// Handle open_modal param — store in session so it survives page reloads
if (isset($_GET['open_modal'])) {
    $_SESSION['open_modal'] = $_GET['open_modal'];
    // Redirect to remove query param
    header("Location: index.php");
    exit;
}

// 1. Handle view switching requests
if (isset($_GET['switch_view'])) {
    $targetView = $_GET['switch_view'];
    $currentUser = get_logged_in_user();
    
    if ($targetView === 'merchant') {
        // Only allow merchants or admins to switch to the merchant dashboard
        if ($currentUser && ($currentUser['role'] === 'merchant' || $currentUser['role'] === 'admin')) {
            $_SESSION['active_view'] = 'merchant';
        }
    } else {
        // Rescuer view is open to everyone
        $_SESSION['active_view'] = 'rescuer';
    }
    
    header("Location: index.php");
    exit;
}

// 2. Identify active view
$currentUser = get_logged_in_user();
$activeView = $_SESSION['active_view'] ?? 'rescuer';

// Security check: Force rescuer view if session active_view is merchant but user is not a merchant
if ($activeView === 'merchant') {
    if (!$currentUser || ($currentUser['role'] !== 'merchant' && $currentUser['role'] !== 'admin')) {
        $_SESSION['active_view'] = 'rescuer';
        $activeView = 'rescuer';
    }
}

// 3. Render Page Structure
include __DIR__ . '/components/header.php';

echo '<main class="flex-grow flex flex-col relative pb-8">';

if ($activeView === 'merchant') {
    include __DIR__ . '/components/merchant_dashboard.php';
} else {
    include __DIR__ . '/components/rescuer_dashboard.php';
}

echo '</main>';

// Include modals overlays (Auth, Reg Merchant, Orders History, Details)
include __DIR__ . '/components/auth_modals.php';

// Auto-open modal from session, then clear it
$openModal = $_SESSION['open_modal'] ?? '';
unset($_SESSION['open_modal']);
if ($openModal && !$currentUser):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var m = '<?= htmlspecialchars($openModal, ENT_QUOTES) ?>';
    if (document.getElementById(m)) openModal(m);
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/components/footer.php'; ?>
