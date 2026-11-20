<?php

declare(strict_types=1);

function e(?string $value): string {

    return htmlspecialchars(
        $value ?? '',
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );

}

function cfg(string $key, mixed $default = null): mixed {

    global $config;

    return $config[$key] ?? $default;

}

function url(string $path = ''): string {

    $base = rtrim(cfg('base_url', ''), '/');

    return $base . '/' . ltrim($path, '/');

}

function redirect(string $path): never {

    header('Location: ' . url($path));

    exit;

}

function flash(string $type, string $message): void {

    $_SESSION['flash'] = [$type, $message];

}

function show_flash(): void {

    if (!empty($_SESSION['flash'])) {

        [$type, $message] = $_SESSION['flash'];

        unset($_SESSION['flash']);

        echo '<div class="notice ' . e($type) . '">'
            . e($message) .
            '</div>';

    }

}

function csrf_token(): string {

    if (empty($_SESSION['csrf'])) {

        $_SESSION['csrf'] = bin2hex(random_bytes(32));

    }

    return $_SESSION['csrf'];

}

function csrf_field(): string {

    return '<input type="hidden" name="csrf" value="'
        . e(csrf_token()) . '">';

}

function verify_csrf(): void {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!hash_equals(
            $_SESSION['csrf'] ?? '',
            $_POST['csrf'] ?? ''
        )) {

            http_response_code(419);

            exit('Session expired. Reload and try again.');

        }

    }

}

function current_user(): ?array {

    global $pdo;

    if (empty($_SESSION['user_id'])) {

        return null;

    }

    $stmt = $pdo->prepare(
        "SELECT id, name, email, phone, role,
        user_type, class_level, profile_pic,
        balance, status
        FROM users
        WHERE id = ? AND status = 'active'"
    );

    $stmt->execute([$_SESSION['user_id']]);

    return $stmt->fetch() ?: null;

}

function require_login(): array {

    $user = current_user();

    if (!$user) {

        flash('error', 'Please login to continue.');

        redirect('login.php');

    }

    return $user;

}

function require_admin(): array {

    $user = require_login();

    if ($user['role'] !== 'admin') {

        http_response_code(403);

        exit('Administrator access required.');

    }

    return $user;

}

function money(int|float|string $amount): string {

    return 'UGX ' . number_format((float)$amount);

}

function notify(
    int $userId,
    string $title,
    string $body
): void {

    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO notifications
        (user_id, title, body)
        VALUES (?, ?, ?)"
    );

    $stmt->execute([
        $userId,
        $title,
        $body
    ]);

}

function audit(
    ?int $userId,
    string $action,
    string $detail = ''
): void {

    global $pdo;

    $stmt = $pdo->prepare(
        "INSERT INTO audit_logs
        (user_id, action, detail, ip_address)
        VALUES (?, ?, ?, ?)"
    );

    $stmt->execute([

        $userId,

        $action,

        $detail,

        substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45)

    ]);

}

function safe_file_name(string $name): string {

    return preg_replace(
        '/[^a-zA-Z0-9._-]/',
        '_',
        basename($name)
    );

}

function paper_price(int $bytes): int {

    if ($bytes <= 200 * 1024 * 1024) {

        return 500;

    }

    return 1000;

}

function page_header(string $title = ''): void {

    $user = current_user();

    echo '<!DOCTYPE html>';

    echo '<html lang="en">';

    echo '<head>';

    echo '<meta charset="UTF-8">';

    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

    echo '<title>' . e($title) . ' | ELITESHELF</title>';

    echo '<link rel="stylesheet" href="' .
        e(url('assets/style.css')) . '">';

    echo '</head>';

    echo '<body>';

    echo '<header class="top">';

    echo '<a class="brand" href="' .
        e(url('index.php')) .
        '">ELITESHELF</a>';

    echo '<nav>';

    echo '<a href="' . e(url('index.php')) . '">Home</a>';

    echo '<a href="' . e(url('about.php')) . '">About Us</a>';

    echo '<a href="' . e(url('contact.php')) . '">Contact Us</a>';

    if ($user) {

        echo '<a href="' . e(url('notifications.php')) . '">Notifications</a>';

        echo '<a href="' . e(url('review.php')) . '">Reviews</a>';

        echo '<a href="' . e(url('dashboard.php')) . '">My Account</a>';

        if ($user['role'] === 'admin') {

            echo '<a href="' . e(url('admin/index.php')) . '">Admin</a>';

        }

        echo '<a href="' . e(url('logout.php')) . '">Logout</a>';

    } else {

        echo '<a class="login" href="' .
            e(url('login.php')) .
            '">Login / Register</a>';

    }

    echo '</nav>';

    echo '</header>';

    echo '<main class="container">';

    show_flash();

}

function page_footer(): void {

    echo '</main>';

    echo '<footer>';

    echo 'ELITESHELF | Unlock Knowledge. Empower Excellence. Shape the Future.';

    echo '<div class="social">';

    echo '<a href="https://www.tiktok.com/">TikTok</a>';

    echo ' | ';

    echo '<a href="mailto:info@yourdomain.com">Email</a>';

    echo ' | ';

    echo '<a href="https://wa.me/256766599663">WhatsApp</a>';

    echo '</div>';

    echo '</footer>';

    echo '<script src="' .
        e(url('assets/app.js')) .
        '"></script>';

    echo '</body></html>';

}

function require_post(): void {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

        http_response_code(405);

        exit('Method not allowed');

    }

    verify_csrf();

}
