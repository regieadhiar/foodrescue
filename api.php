<?php
// api.php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/api_handler.php';

// Decode JSON input if available, merge with $_POST
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true) ?: [];
$requestData = array_merge($_POST, $_GET, $jsonData);

$action = $requestData['action'] ?? '';

if (empty($action)) {
    send_json(false, 'Parameter aksi tidak ditentukan.');
}

// CSRF protection for state-changing (POST) requests
$stateChangingActions = ['login', 'register', 'logout', 'register_merchant', 'add_food_item', 'claim_food_item', 'toggle_merchant_status', 'forgot_password', 'reset_password', 'verify_qr_claim'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, $stateChangingActions)) {
    $csrfToken = $requestData['csrf_token'] ?? '';
    if (!validate_csrf_token($csrfToken)) {
        send_json(false, 'Sesi tidak valid. Silakan muat ulang halaman.');
    }
}

// Router for actions
switch ($action) {
    case 'login':
        api_login($requestData);
        break;
        
    case 'register':
        api_register($requestData);
        break;
        
    case 'logout':
        api_logout();
        break;
        
    case 'register_merchant':
        api_register_merchant($requestData);
        break;
        
    case 'add_food_item':
        api_add_food_item($requestData);
        break;
        
    case 'get_food_items':
        api_get_food_items();
        break;
        
    case 'claim_food_item':
        api_claim_food_item($requestData);
        break;
        
    case 'toggle_merchant_status':
        api_toggle_merchant($requestData);
        break;
        
    case 'get_rescuer_orders':
        api_get_rescuer_orders();
        break;
        
    case 'verify_qr_claim':
        api_verify_qr_claim($requestData);
        break;
        
    case 'get_merchants':
        api_get_merchants();
        break;
        
    case 'forgot_password':
        api_forgot_password($requestData);
        break;
        
    case 'reset_password':
        api_reset_password($requestData);
        break;
        
    case 'get_stats':
        api_get_stats();
        break;
        
    case 'get_initial_data':
        api_get_initial_data();
        break;
        
    default:
        send_json(false, 'Aksi tidak dikenal.');
        break;
}
