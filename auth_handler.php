<?php
require_once __DIR__ . '/classes/Auth.php';

header('Content-Type: application/json');

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

$auth = new Auth();

try {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            $result = $auth->login($username, $password, $rememberMe);
            echo json_encode($result);
            break;

        case 'signup':
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $userId = $auth->createUniqueId();
            
            if (!$email) {
                echo json_encode(['success' => false, 'message' => 'Invalid email address']);
                break;
            }
            
            if ($password !== $confirmPassword) {
                echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
                break;
            }
            
            $result = $auth->register($userId, $username, $email, $password);
            echo json_encode($result);
            break;

        case 'logout':
            $auth->logout();
            echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
            break;

        case 'check_auth':
            $response = [
                'logged_in' => $auth->isLoggedIn(),
                'success' => true
            ];
            if ($response['logged_in']) {
                $response['roles'] = $auth->getUserRoles();
                $response['username'] = $_SESSION['username'] ?? '';
            }
            echo json_encode($response);
            break;

        case 'get_user_roles':
            if (!$auth->isLoggedIn()) {
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                break;
            }
            $roles = $auth->getUserRoles();
            echo json_encode(['success' => true, 'roles' => $roles]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }

} catch (Exception $e) {
    error_log("Auth handler error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>