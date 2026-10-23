<?php
declare(strict_types=1);

function backend_load_env(): array
{
    static $environment = null;
    if ($environment !== null) {
        return $environment;
    }

    $environment = [];
    $environmentPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.local';
    if (is_file($environmentPath)) {
        $lines = file($environmentPath, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('Unable to read .env.local.');
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
                continue;
            }
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name)) {
                continue;
            }
            if (strlen($value) >= 2 && (($value[0] === '"' && substr($value, -1) === '"') || ($value[0] === "'" && substr($value, -1) === "'"))) {
                $value = substr($value, 1, -1);
            }
            if (getenv($name) === false) {
                $environment[$name] = $value;
            }
        }
    }

    foreach (['SUPABASE_URL', 'SUPABASE_ANON_KEY', 'SUPABASE_SERVICE_ROLE_KEY', 'APP_BASE_URL'] as $name) {
        $value = getenv($name);
        if ($value !== false) {
            $environment[$name] = $value;
        }
    }

    return $environment;
}

function backend_config(): array
{
    $environment = backend_load_env();
    foreach (['SUPABASE_URL', 'SUPABASE_ANON_KEY', 'SUPABASE_SERVICE_ROLE_KEY'] as $required) {
        if (empty($environment[$required])) {
            throw new RuntimeException('Supabase configuration is incomplete. Set ' . $required . ' in .env.local.');
        }
    }

    $url = rtrim($environment['SUPABASE_URL'], '/');
    $scheme = parse_url($url, PHP_URL_SCHEME);
    $host = parse_url($url, PHP_URL_HOST);
    $isLocalHttp = $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    if (!filter_var($url, FILTER_VALIDATE_URL) || ($scheme !== 'https' && !$isLocalHttp)) {
        throw new RuntimeException('SUPABASE_URL must use HTTPS (HTTP is allowed only for a local Supabase instance).');
    }

    return [
        'url' => $url,
        'anon_key' => $environment['SUPABASE_ANON_KEY'],
        'service_role_key' => $environment['SUPABASE_SERVICE_ROLE_KEY'],
        'base_url' => rtrim($environment['APP_BASE_URL'] ?? '', '/'),
    ];
}

function backend_http_request(string $url, string $method, array $headers, ?array $payload = null): array
{
    $headerLines = [];
    foreach ($headers as $name => $value) {
        $headerLines[] = $name . ': ' . $value;
    }
    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headerLines),
            'ignore_errors' => true,
            'timeout' => 15,
            'protocol_version' => 1.1,
        ],
    ];
    if ($payload !== null) {
        $options['http']['content'] = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    $context = stream_context_create($options);
    $responseBody = @file_get_contents($url, false, $context);
    if ($responseBody === false) {
        throw new RuntimeException('Unable to connect to the configured backend service.');
    }

    $responseHeaders = $http_response_header ?? [];
    $statusCode = 0;
    $parsedHeaders = [];
    foreach ($responseHeaders as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $match)) {
            $statusCode = (int) $match[1];
        } elseif (strpos($header, ':') !== false) {
            [$name, $value] = explode(':', $header, 2);
            $parsedHeaders[strtolower(trim($name))] = trim($value);
        }
    }

    $decodedBody = null;
    if ($responseBody !== '') {
        $decodedBody = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $decodedBody = $responseBody;
        }
    }

    return ['status' => $statusCode, 'headers' => $parsedHeaders, 'body' => $decodedBody];
}

function backend_supabase_request(
    string $method,
    string $path,
    ?array $payload = null,
    bool $admin = false,
    ?string $accessToken = null,
    ?string $prefer = null
): array {
    $config = backend_config();
    $key = $admin ? $config['service_role_key'] : $config['anon_key'];
    $authorization = $accessToken ?? ($admin ? $config['service_role_key'] : $config['anon_key']);
    $headers = [
        'apikey' => $key,
        'Authorization' => 'Bearer ' . $authorization,
        'Accept' => 'application/json',
    ];
    if ($payload !== null) {
        $headers['Content-Type'] = 'application/json';
    }
    if ($prefer !== null) {
        $headers['Prefer'] = $prefer;
    }

    $response = backend_http_request($config['url'] . $path, $method, $headers, $payload);
    if ($response['status'] < 200 || $response['status'] >= 300) {
        $detail = is_array($response['body'])
            ? (string) ($response['body']['msg'] ?? $response['body']['message'] ?? $response['body']['error_description'] ?? $response['body']['error'] ?? '')
            : '';
        throw new RuntimeException('Supabase request failed (' . $response['status'] . ')' . ($detail !== '' ? ': ' . substr($detail, 0, 300) : '.'));
    }

    return $response;
}

function backend_json_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function backend_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function backend_redirect(string $location, int $statusCode = 303): void
{
    header('Location: ' . $location, true, $statusCode);
    exit;
}

function backend_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');
    $environment = backend_load_env();
    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || parse_url($environment['APP_BASE_URL'] ?? '', PHP_URL_SCHEME) === 'https';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function backend_csrf_token(): string
{
    backend_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function backend_verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], $submitted)) {
        http_response_code(403);
        throw new RuntimeException('The form expired. Reload the page and try again.');
    }
}

function backend_csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . backend_escape(backend_csrf_token()) . '">';
}

function backend_admin_user(): ?array
{
    backend_start_session();
    $user = $_SESSION['admin_user'] ?? null;
    return is_array($user) && isset($user['id'], $user['email']) ? $user : null;
}

function backend_admin_is_active(array $user): bool
{
    $id = (string) $user['id'];
    if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
        return false;
    }
    $response = backend_supabase_request(
        'GET',
        '/rest/v1/admin_users?select=user_id&user_id=eq.' . rawurlencode($id) . '&is_active=eq.true&limit=1',
        null,
        true
    );
    return is_array($response['body']) && count($response['body']) === 1;
}

function backend_require_admin(): array
{
    $user = backend_admin_user();
    if ($user === null) {
        backend_redirect('/admin/login');
    }
    $now = time();
    $authenticatedAt = (int) ($_SESSION['admin_authenticated_at'] ?? 0);
    $lastActivityAt = (int) ($_SESSION['admin_last_activity_at'] ?? 0);
    if ($authenticatedAt === 0 || $lastActivityAt === 0 || $now - $authenticatedAt > 28800 || $now - $lastActivityAt > 3600) {
        $_SESSION = [];
        session_regenerate_id(true);
        backend_redirect('/admin/login?expired=1');
    }
    if (!backend_admin_is_active($user)) {
        $_SESSION = [];
        session_regenerate_id(true);
        backend_redirect('/admin/login?expired=1');
    }
    $_SESSION['admin_last_activity_at'] = $now;
    return $user;
}

function backend_auth_login(string $email, string $password): array
{
    $response = backend_supabase_request(
        'POST',
        '/auth/v1/token?grant_type=password',
        ['email' => $email, 'password' => $password]
    );
    $body = $response['body'];
    if (!is_array($body) || empty($body['user']['id']) || empty($body['user']['email'])) {
        throw new RuntimeException('Supabase Auth returned an incomplete login response.');
    }

    return ['id' => (string) $body['user']['id'], 'email' => (string) $body['user']['email']];
}

function backend_auth_password_recovery(string $email): void
{
    $config = backend_config();
    if ($config['base_url'] === '') {
        throw new RuntimeException('Set APP_BASE_URL in .env.local before using password recovery.');
    }
    backend_supabase_request('POST', '/auth/v1/recover?redirect_to=' . rawurlencode($config['base_url'] . '/admin/reset-password'), ['email' => $email]);
}

function backend_auth_update_password(string $accessToken, string $password): void
{
    if (strlen($password) < 12 || strlen($password) > 128) {
        throw new InvalidArgumentException('Choose a password between 12 and 128 characters.');
    }
    backend_supabase_request(
        'PUT',
        '/auth/v1/user',
        ['password' => $password],
        false,
        $accessToken
    );
}

function backend_rate_limit_key(): ?string
{
    $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($remoteAddress === '') {
        return null;
    }
    $config = backend_config();
    return hash_hmac('sha256', $remoteAddress, $config['service_role_key']);
}

function backend_handle_contact(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        backend_json_response(405, ['message' => 'This endpoint accepts form submissions only.']);
    }

    $honeypot = trim((string) ($_POST['website'] ?? ''));
    if ($honeypot !== '') {
        backend_json_response(200, ['message' => 'Thank you. Your enquiry has been received.']);
    }

    $name = trim((string) ($_POST['name'] ?? ''));
    $company = trim((string) ($_POST['company'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $service = trim((string) ($_POST['service'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $privacy = (string) ($_POST['privacy'] ?? '');

    if (
        strlen($name) < 2 || strlen($name) > 100
        || strlen($company) > 150
        || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($phone) < 7 || strlen($phone) > 40 || !preg_match('/^[+0-9\s().-]+$/', $phone)
        || !preg_match('/^[a-z0-9-]{1,80}$/', $service)
        || strlen($message) < 10 || strlen($message) > 3000
        || $privacy !== 'yes'
    ) {
        backend_json_response(422, ['message' => 'Please check the form details and consent before submitting.']);
    }

    $ipHash = backend_rate_limit_key();
    if ($ipHash !== null) {
        $since = gmdate('Y-m-d\TH:i:s\Z', time() - 3600);
        $rateResponse = backend_supabase_request(
            'GET',
            '/rest/v1/contact_submissions?select=id&source_ip_hash=eq.' . rawurlencode($ipHash)
                . '&created_at=gte.' . rawurlencode($since) . '&limit=3',
            null,
            true
        );
        if (is_array($rateResponse['body']) && count($rateResponse['body']) >= 3) {
            backend_json_response(429, ['message' => 'Please wait before sending another enquiry. You can call 045 254212 or email info@acctechs.ie.']);
        }
    }

    backend_supabase_request(
        'POST',
        '/rest/v1/contact_submissions',
        [
            'name' => $name,
            'company' => $company,
            'email' => $email,
            'phone' => $phone,
            'service' => $service,
            'message' => $message,
            'privacy_accepted' => true,
            'source_ip_hash' => $ipHash,
        ],
        true,
        null,
        'return=minimal'
    );
    backend_json_response(201, ['message' => 'Thank you. Your enquiry has been received.']);
}

function backend_fetch_jobs(bool $publicOnly): array
{
    $query = '/rest/v1/career_jobs?select=id,title,slug,location,employment_type,summary,description,requirements,status,created_at,updated_at';
    if ($publicOnly) {
        $query .= '&status=eq.open';
    }
    $query .= '&order=created_at.desc&limit=' . ($publicOnly ? '100' : '1000');
    $response = backend_supabase_request('GET', $query, null, true);
    if (!is_array($response['body'])) {
        throw new RuntimeException('Supabase returned an invalid job listing.');
    }
    return $response['body'];
}

function backend_job_public_markup(array $jobs): string
{
    if (count($jobs) === 0) {
        return '<span class="eyebrow">Future opportunities</span><h2>Bring your perspective.</h2><p>No vacancies are currently advertised. For information about future opportunities, please contact us first.</p><a class="button" href="mailto:info@acctechs.ie?subject=Careers%20enquiry">Make a careers enquiry</a>';
    }

    $markup = '<span class="eyebrow">Current opportunities</span><h2>Find your next opportunity.</h2><div class="career-job-list">';
    foreach ($jobs as $job) {
        $markup .= '<article class="career-job"><h3>' . backend_escape($job['title'] ?? '') . '</h3>';
        $meta = array_filter([
            (string) ($job['location'] ?? ''),
            (string) ($job['employment_type'] ?? ''),
        ]);
        if ($meta !== []) {
            $markup .= '<p class="career-job-meta">' . backend_escape(implode(' · ', $meta)) . '</p>';
        }
        if (!empty($job['summary'])) {
            $markup .= '<p>' . backend_escape($job['summary']) . '</p>';
        }
        if (!empty($job['description'])) {
            $markup .= '<details><summary>Role description</summary><p>' . nl2br(backend_escape($job['description'])) . '</p></details>';
        }
        $requirements = preg_split('/\r\n|\r|\n/', (string) ($job['requirements'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        if (is_array($requirements) && $requirements !== []) {
            $markup .= '<details><summary>Requirements</summary><ul>';
            foreach ($requirements as $requirement) {
                $markup .= '<li>' . backend_escape(trim($requirement)) . '</li>';
            }
            $markup .= '</ul></details>';
        }
        $subject = rawurlencode('Careers enquiry: ' . (string) ($job['title'] ?? 'Open role'));
        $markup .= '<a class="button" href="mailto:info@acctechs.ie?subject=' . $subject . '">Enquire about this role</a></article>';
    }
    return $markup . '</div>';
}

function backend_public_jobs_markup(string $markup): string
{
    $jobs = backend_fetch_jobs(true);
    $updated = preg_replace_callback(
        '/<div\b(?=[^>]*\bclass="[^"]*\bcareer-panel\b)[^>]*>[\s\S]*?<\/div>/i',
        static function () use ($jobs): string {
            return '<div class="career-panel">' . backend_job_public_markup($jobs) . '</div>';
        },
        $markup,
        1,
        $count
    );
    if ($updated === null || $count !== 1) {
        throw new RuntimeException('Unable to render current career vacancies.');
    }
    return $updated;
}

function backend_unavailable_jobs_markup(string $markup): string
{
    $updated = preg_replace(
        '/<div\b(?=[^>]*\bclass="[^"]*\bcareer-panel\b)[^>]*>[\s\S]*?<\/div>/i',
        '<div class="career-panel"><span class="eyebrow">Current opportunities</span><h2>Career listings are temporarily unavailable.</h2><p>Please contact us at <a href="mailto:info@acctechs.ie">info@acctechs.ie</a> for information about current opportunities.</p></div>',
        $markup,
        1,
        $count
    );
    if ($updated === null || $count !== 1) {
        throw new RuntimeException('Unable to render the career listings status.');
    }
    return $updated;
}

function backend_request_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    return is_string($path) ? rawurldecode($path) : '/';
}

function backend_dispatch(): void
{
    backend_start_session();
    $path = backend_request_path();
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    if ($path === '/api/contact') {
        try {
            backend_handle_contact();
        } catch (Throwable $exception) {
            error_log('Contact submission failed: ' . $exception->getMessage());
            backend_json_response(503, ['message' => 'Your enquiry could not be saved right now. Please call 045 254212 or email info@acctechs.ie.']);
        }
    }

    if ($path === '/api/careers') {
        if ($method !== 'GET') {
            header('Allow: GET');
            backend_json_response(405, ['message' => 'This endpoint accepts GET requests only.']);
        }
        try {
            backend_json_response(200, ['jobs' => backend_fetch_jobs(true)]);
        } catch (Throwable $exception) {
            error_log('Public career listing failed: ' . $exception->getMessage());
            backend_json_response(503, ['message' => 'Career opportunities are temporarily unavailable.']);
        }
    }

    if ($path === '/api/auth/forgot-password') {
        if ($method !== 'POST') {
            header('Allow: POST');
            backend_json_response(405, ['message' => 'This endpoint accepts POST requests only.']);
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            backend_json_response(422, ['message' => 'Enter a valid email address.']);
        }
        try {
            backend_auth_password_recovery($email);
            backend_json_response(200, ['message' => 'If an administrator account exists for that address, a password reset email has been sent.']);
        } catch (Throwable $exception) {
            error_log('Password recovery request failed: ' . $exception->getMessage());
            backend_json_response(503, ['message' => 'Password recovery is temporarily unavailable. Please contact the site owner.']);
        }
    }

    if ($path === '/api/auth/reset-password') {
        if ($method !== 'POST') {
            header('Allow: POST');
            backend_json_response(405, ['message' => 'This endpoint accepts POST requests only.']);
        }
        $token = trim((string) ($_POST['access_token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if ($token === '' || strlen($token) > 4096) {
            backend_json_response(422, ['message' => 'Open the password reset link from your email and try again.']);
        }
        try {
            backend_auth_update_password($token, $password);
            backend_json_response(200, ['message' => 'Your password has been updated. You can now sign in.']);
        } catch (InvalidArgumentException $exception) {
            backend_json_response(422, ['message' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            error_log('Password reset failed: ' . $exception->getMessage());
            backend_json_response(400, ['message' => 'This reset link is invalid or expired. Request a new password reset email.']);
        }
    }

    if (strpos($path, '/admin') === 0) {
        backend_handle_admin_route($path, $method);
    }
}

function backend_admin_icon(string $name): string
{
    $paths = [
        'overview' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'contacts' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'jobs' => '<rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18M10 12v2h4v-2"/>',
        'admins' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M19 8v6M16 11h6"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
    ];
    $content = $paths[$name] ?? '';
    return '<svg class="admin-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $content . '</svg>';
}

function backend_admin_shell(string $title, string $content, ?string $active = null, bool $loggedIn = true): void
{
    $user = backend_admin_user();
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="en-IE"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>'
        . backend_escape($title) . ' | ACCTS &amp; TECHS Admin</title><link rel="preload" href="/assets/fonts/manrope-latin.woff2" as="font" type="font/woff2" crossorigin><link rel="stylesheet" href="/assets/css/site.css"><link rel="stylesheet" href="/assets/css/admin.css"><script type="module" src="/assets/js/admin.js"></script></head><body class="admin-body">';
    if ($loggedIn && $user !== null) {
        $navigation = [
            'dashboard' => ['Overview', '/admin/dashboard', 'overview'],
            'contacts' => ['Contact enquiries', '/admin/contacts', 'contacts'],
            'jobs' => ['Careers', '/admin/jobs', 'jobs'],
            'admins' => ['Administrators', '/admin/admins', 'admins'],
        ];
        echo '<div class="admin-shell"><aside class="admin-sidebar"><a class="admin-brand" href="/admin/dashboard"><img src="/assets/images/at-mark.png" alt="" width="38" height="38"><span><strong>ACCTS &amp; TECHS</strong><small>Administration</small></span></a><span class="admin-nav-heading">WORKSPACE</span><nav aria-label="Admin navigation">';
        foreach ($navigation as $key => [$label, $href, $icon]) {
            $isActive = $active === $key;
            echo '<a class="admin-nav-link' . ($isActive ? ' is-active' : '') . '" href="' . backend_escape($href) . '"'
                . ($isActive ? ' aria-current="page"' : '') . '>' . backend_admin_icon($icon) . '<span>'
                . backend_escape($label) . '</span></a>';
        }
        echo '</nav><form method="post" action="/admin/logout" class="admin-sidebar-bottom">'
            . backend_csrf_input() . '<button class="admin-nav-link admin-logout" type="submit">' . backend_admin_icon('logout') . '<span>Sign out</span></button></form></aside><main class="admin-main"><header class="admin-topbar"><div><span class="admin-kicker">ACCTS &amp; TECHS LTD</span><h1>' . backend_escape($title) . '</h1></div><span class="admin-user"><span class="admin-user-avatar" aria-hidden="true">' . backend_escape(strtoupper(substr((string) ($user['email'] ?? 'A'), 0, 1))) . '</span><span>' . backend_escape($user['email'] ?? '') . '</span></span></header>';
    } else {
        echo '<main class="admin-auth-main">';
    }

    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    if (is_array($flash) && !empty($flash['message'])) {
        echo '<div class="admin-flash admin-flash-' . backend_escape($flash['type'] ?? 'success') . '" role="status">' . backend_escape($flash['message']) . '</div>';
    }
    echo $content;
    echo $loggedIn && $user !== null ? '</main></div>' : '</main>';
    echo '</body></html>';
    exit;
}

function backend_admin_auth_page(string $title, string $content): void
{
    $body = '<a class="admin-auth-brand" href="/"><img src="/assets/images/at-mark.png" width="42" height="42" alt=""><span><strong>ACCTS &amp; TECHS</strong><small>Admin portal</small></span></a><section class="admin-auth-card"><span class="admin-kicker">SECURE ADMIN AREA</span><h1>' . backend_escape($title) . '</h1>' . $content . '</section><p class="admin-auth-footer"><a href="/">Return to website</a></p>';
    backend_admin_shell($title, $body, null, false);
}

function backend_set_flash(string $message, string $type = 'success'): void
{
    $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
}

function backend_admin_rows_count(string $table, string $filter = '', string $identifier = 'id'): int
{
    $response = backend_supabase_request(
        'GET',
        '/rest/v1/' . $table . '?select=' . rawurlencode($identifier) . $filter . '&limit=1',
        null,
        true,
        null,
        'count=exact'
    );
    $range = $response['headers']['content-range'] ?? '';
    if (preg_match('/\/(\d+)$/', $range, $match)) {
        return (int) $match[1];
    }
    return is_array($response['body']) ? count($response['body']) : 0;
}

function backend_admin_count_result(string $table, string $filter = '', string $identifier = 'id'): array
{
    try {
        return ['count' => backend_admin_rows_count($table, $filter, $identifier), 'error' => null];
    } catch (Throwable $exception) {
        error_log('Admin dashboard count failed for ' . $table . ': ' . $exception->getMessage());
        return ['count' => null, 'error' => $exception];
    }
}

function backend_admin_data_error_message(Throwable $exception): string
{
    $message = strtolower($exception->getMessage());
    if (
        strpos($message, 'pgrst205') !== false
        || strpos($message, '42p01') !== false
        || strpos($message, 'schema cache') !== false
        || strpos($message, 'does not exist') !== false
    ) {
        return 'Supabase tables are missing or not in the API schema cache. Run the project migration in the Supabase SQL Editor, then refresh the schema cache.';
    }
    if (strpos($message, '(401)') !== false || strpos($message, '(403)') !== false || strpos($message, 'permission denied') !== false) {
        return 'Supabase rejected the server credentials or database permissions. Check the service-role key in .env.local and confirm the migration grants service_role access.';
    }
    if (strpos($message, 'unable to connect') !== false || strpos($message, 'timed out') !== false) {
        return 'The server could not connect to Supabase. Check the project URL and the server network connection.';
    }
    if (strpos($message, 'supabase configuration') !== false) {
        return 'Supabase configuration is incomplete. Check SUPABASE_URL, SUPABASE_ANON_KEY and SUPABASE_SERVICE_ROLE_KEY in .env.local.';
    }
    return 'A Supabase data request failed. Check the PHP error log for the exact server-side error, then verify the migration and .env.local settings.';
}

function backend_handle_admin_route(string $path, string $method): void
{
    if ($path === '/admin') {
        backend_redirect(backend_admin_user() ? '/admin/dashboard' : '/admin/login', 302);
    }

    if ($path === '/admin/login' && $method === 'POST') {
        try {
            backend_verify_csrf();
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $password = (string) ($_POST['password'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 256) {
                throw new InvalidArgumentException('Enter a valid email address and password.');
            }
            $user = backend_auth_login($email, $password);
            if (!backend_admin_is_active($user)) {
                throw new RuntimeException('This account is not an active administrator.');
            }
            session_regenerate_id(true);
            $_SESSION['admin_user'] = $user;
            $_SESSION['admin_authenticated_at'] = time();
            $_SESSION['admin_last_activity_at'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            backend_redirect('/admin/dashboard');
        } catch (Throwable $exception) {
            error_log('Admin sign-in failed: ' . $exception->getMessage());
            $_SESSION['admin_flash'] = ['message' => 'Sign-in failed. Check your credentials and administrator access.', 'type' => 'error'];
            backend_redirect('/admin/login');
        }
    }

    if ($path === '/admin/login' && $method === 'GET') {
        $expiredMessage = isset($_GET['expired']) ? '<p class="admin-form-note">Your administrator access has expired. Please sign in again.</p>' : '';
        backend_admin_auth_page('Admin sign in', $expiredMessage . '<form method="post" action="/admin/login" class="admin-form">'
            . backend_csrf_input() . '<label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="username" maxlength="254" required><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required><button class="admin-button" type="submit">Sign in</button><a class="admin-auth-link" href="/admin/forgot-password">Forgot your password?</a></form>');
    }

    if ($path === '/admin/forgot-password' && $method === 'GET') {
        backend_admin_auth_page('Reset password', '<p class="admin-form-note">Enter your administrator email address and we will send a secure password reset link if the account exists.</p><form data-auth-form method="post" action="/api/auth/forgot-password" class="admin-form">'
            . '<label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" maxlength="254" required><button class="admin-button" type="submit">Send reset link</button><p class="admin-form-status" role="status"></p><a class="admin-auth-link" href="/admin/login">Back to sign in</a></form>');
    }

    if ($path === '/admin/reset-password' && $method === 'GET') {
        backend_admin_auth_page('Choose a new password', '<p class="admin-form-note">Use the secure link in your email to set a new administrator password.</p><form data-password-reset method="post" action="/api/auth/reset-password" class="admin-form">'
            . '<input type="hidden" name="access_token" value=""><label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="128" required><label for="confirm-password">Confirm new password</label><input id="confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="128" required><button class="admin-button" type="submit">Update password</button><p class="admin-form-status" role="status"></p><a class="admin-auth-link" href="/admin/login">Back to sign in</a></form>');
    }

    if ($path === '/admin/logout') {
        if ($method !== 'POST') {
            header('Allow: POST');
            http_response_code(405);
            exit('Method not allowed.');
        }
        backend_verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        backend_redirect('/admin/login');
    }

    $user = backend_require_admin();
    if ($method === 'POST') {
        try {
            backend_verify_csrf();
            backend_handle_admin_action($path, $user);
        } catch (Throwable $exception) {
            error_log('Admin operation failed: ' . $exception->getMessage());
            backend_set_flash('The requested change could not be completed. Please review the details and try again.', 'error');
            backend_redirect($path === '/admin/jobs' ? '/admin/jobs' : $path);
        }
    }
    if ($method !== 'GET') {
        header('Allow: GET, POST');
        http_response_code(405);
        exit('Method not allowed.');
    }

    try {
        if ($path === '/admin/dashboard') {
            backend_render_admin_dashboard();
        }
        if ($path === '/admin/contacts') {
            backend_render_admin_contacts();
        }
        if ($path === '/admin/jobs') {
            backend_render_admin_jobs();
        }
        if ($path === '/admin/admins') {
            backend_render_admin_admins($user);
        }
    } catch (Throwable $exception) {
        error_log('Admin page failed: ' . $exception->getMessage());
        http_response_code(503);
        $message = backend_admin_data_error_message($exception);
        backend_admin_shell(
            'Dashboard connection issue',
            '<section class="admin-panel admin-error-panel"><span class="admin-kicker">DATA CONNECTION</span><h2>We could not load this section.</h2><p>'
                . backend_escape($message) . '</p><a class="admin-button" href="' . backend_escape($path) . '">Try again</a></section>'
        );
    }

    http_response_code(404);
    backend_admin_shell('Page not found', '<section class="admin-panel"><p>The requested admin page could not be found.</p></section>');
}

function backend_handle_admin_action(string $path, array $user): void
{
    $action = (string) ($_POST['action'] ?? '');
    if ($path === '/admin/contacts' && $action === 'delete-contact') {
        $id = (string) ($_POST['id'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
            throw new InvalidArgumentException('Invalid enquiry identifier.');
        }
        backend_supabase_request('DELETE', '/rest/v1/contact_submissions?id=eq.' . rawurlencode($id), null, true, null, 'return=minimal');
        backend_set_flash('Contact enquiry deleted.');
        backend_redirect('/admin/contacts');
    }

    if ($path === '/admin/jobs' && $action === 'save-job') {
        $id = trim((string) ($_POST['id'] ?? ''));
        $title = trim((string) ($_POST['title'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $employmentType = trim((string) ($_POST['employment_type'] ?? ''));
        $summary = trim((string) ($_POST['summary'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $requirements = trim((string) ($_POST['requirements'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'draft');
        if (
            strlen($title) < 2 || strlen($title) > 150
            || strlen($location) > 120 || strlen($employmentType) > 80
            || strlen($summary) > 500 || strlen($description) > 10000
            || strlen($requirements) > 10000 || !in_array($status, ['draft', 'open', 'closed'], true)
        ) {
            throw new InvalidArgumentException('Check the job details and character limits.');
        }
        $payload = [
            'title' => $title,
            'slug' => backend_job_slug($title),
            'location' => $location,
            'employment_type' => $employmentType,
            'summary' => $summary,
            'description' => $description,
            'requirements' => $requirements,
            'status' => $status,
        ];
        if ($id !== '') {
            if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
                throw new InvalidArgumentException('Invalid job identifier.');
            }
            backend_supabase_request('PATCH', '/rest/v1/career_jobs?id=eq.' . rawurlencode($id), $payload, true, null, 'return=minimal');
            backend_set_flash('Job updated.');
        } else {
            backend_supabase_request('POST', '/rest/v1/career_jobs', $payload, true, null, 'return=minimal');
            backend_set_flash('Job created.');
        }
        backend_redirect('/admin/jobs');
    }

    if ($path === '/admin/jobs' && $action === 'delete-job') {
        $id = (string) ($_POST['id'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
            throw new InvalidArgumentException('Invalid job identifier.');
        }
        backend_supabase_request('DELETE', '/rest/v1/career_jobs?id=eq.' . rawurlencode($id), null, true, null, 'return=minimal');
        backend_set_flash('Job deleted.');
        backend_redirect('/admin/jobs');
    }

    if ($path === '/admin/admins' && $action === 'invite-admin') {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
            throw new InvalidArgumentException('Enter a valid administrator email address.');
        }
        $config = backend_config();
        $redirectTo = $config['base_url'] !== '' ? $config['base_url'] . '/admin/reset-password' : '';
        $invitePath = '/auth/v1/invite?redirect_to=' . rawurlencode($redirectTo);
        $invite = backend_supabase_request('POST', $invitePath, ['email' => $email], true);
        $userId = (string) ($invite['body']['id'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $userId)) {
            throw new RuntimeException('The Auth invite did not return a valid user identifier.');
        }
        try {
            backend_supabase_request('POST', '/rest/v1/admin_users', ['user_id' => $userId, 'email' => $email], true, null, 'return=minimal');
        } catch (Throwable $exception) {
            try {
                backend_supabase_request('DELETE', '/auth/v1/admin/users/' . rawurlencode($userId), null, true);
            } catch (Throwable $cleanupException) {
                error_log('Failed to roll back an administrator invite: ' . $cleanupException->getMessage());
            }
            throw $exception;
        }
        backend_set_flash('Administrator invitation sent. The recipient can use the email link to set a password.');
        backend_redirect('/admin/admins');
    }

    if ($path === '/admin/admins' && $action === 'remove-admin') {
        $id = (string) ($_POST['id'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i', $id)) {
            throw new InvalidArgumentException('Invalid administrator identifier.');
        }
        if (strtolower($id) === strtolower((string) $user['id'])) {
            throw new InvalidArgumentException('You cannot remove your own administrator access.');
        }
        if (backend_admin_rows_count('admin_users', '', 'user_id') <= 1) {
            throw new InvalidArgumentException('At least one administrator must remain.');
        }
        backend_supabase_request('DELETE', '/auth/v1/admin/users/' . rawurlencode($id), null, true);
        backend_set_flash('Administrator access removed.');
        backend_redirect('/admin/admins');
    }

    throw new InvalidArgumentException('Unsupported admin operation.');
}

function backend_job_slug(string $title): string
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim(is_string($slug) ? $slug : '', '-');
    return $slug !== '' ? substr($slug, 0, 140) : 'open-role';
}

function backend_render_admin_dashboard(): void
{
    $cards = [
        [
            'label' => 'Contact enquiries',
            'result' => backend_admin_count_result('contact_submissions'),
            'href' => '/admin/contacts',
            'action' => 'Review enquiries',
            'icon' => 'contacts',
            'tone' => 'blue',
        ],
        [
            'label' => 'Open opportunities',
            'result' => backend_admin_count_result('career_jobs', '&status=eq.open'),
            'href' => '/admin/jobs',
            'action' => 'Manage careers',
            'icon' => 'jobs',
            'tone' => 'green',
        ],
        [
            'label' => 'Administrators',
            'result' => backend_admin_count_result('admin_users', '', 'user_id'),
            'href' => '/admin/admins',
            'action' => 'Manage access',
            'icon' => 'admins',
            'tone' => 'purple',
        ],
    ];
    $failedSections = [];
    $content = '<section class="admin-dashboard-intro"><div><span class="admin-kicker">YOUR WORKSPACE</span><h2>Welcome back.</h2><p>Here’s a clear view of your enquiries, career opportunities and team access.</p></div><a class="admin-button admin-button-secondary" href="/" target="_blank" rel="noopener">View website <span aria-hidden="true">↗</span></a></section><section class="admin-stat-grid" aria-label="Workspace summary">';
    foreach ($cards as $card) {
        $result = $card['result'];
        $countLabel = $result['count'] === null ? '—' : (string) $result['count'];
        $content .= '<article class="admin-stat-card admin-stat-' . backend_escape($card['tone']) . '"><div class="admin-stat-heading"><span class="admin-stat-icon">'
            . backend_admin_icon($card['icon']) . '</span><span class="admin-stat-label">' . backend_escape($card['label']) . '</span></div><strong>'
            . backend_escape($countLabel) . '</strong><div class="admin-stat-footer">';
        if ($result['error'] !== null) {
            $failedSections[] = $card['label'];
            $content .= '<span class="admin-stat-unavailable">Could not load this count</span>';
        } else {
            $content .= '<span class="admin-stat-caption">' . ($result['count'] === 1 ? 'Record' : 'Records') . ' in your workspace</span>';
        }
        $content .= '<a href="' . backend_escape($card['href']) . '">' . backend_escape($card['action']) . ' <span aria-hidden="true">→</span></a></div></article>';
    }
    $content .= '</section>';
    if ($failedSections !== []) {
        $connectionMessage = backend_admin_data_error_message($cards[array_search($failedSections[0], array_column($cards, 'label'), true)]['result']['error']);
        $content .= '<section class="admin-flash admin-flash-error admin-dashboard-warning" role="status"><div><strong>Some dashboard data could not be loaded.</strong><p>'
            . backend_escape(implode(', ', $failedSections)) . ' unavailable. ' . backend_escape($connectionMessage) . '</p></div></section>';
    }
    $content .= '<section class="admin-dashboard-lower"><article class="admin-panel admin-welcome"><span class="admin-kicker">QUICK START</span><h2>What would you like to do?</h2><p>Pick up where you need to be. Changes to open jobs appear on the public careers page.</p><div class="admin-quick-links"><a href="/admin/contacts">' . backend_admin_icon('contacts') . '<span><strong>Review enquiries</strong><small>Read and manage website messages</small></span><span class="admin-quick-arrow" aria-hidden="true">→</span></a><a href="/admin/jobs">' . backend_admin_icon('jobs') . '<span><strong>Post a job</strong><small>Create or update a career opportunity</small></span><span class="admin-quick-arrow" aria-hidden="true">→</span></a><a href="/admin/admins">' . backend_admin_icon('admins') . '<span><strong>Manage your team</strong><small>Invite or remove administrators</small></span><span class="admin-quick-arrow" aria-hidden="true">→</span></a></div></article><aside class="admin-dashboard-tip"><span class="admin-tip-icon" aria-hidden="true">i</span><span class="admin-kicker">A QUICK NOTE</span><h2>Keep your listings current.</h2><p>Only opportunities set to <strong>Open</strong> are visible on your careers page. Draft and closed roles stay private.</p><a href="/admin/jobs">Go to careers <span aria-hidden="true">→</span></a></aside></section>';
    backend_admin_shell('Overview', $content, 'dashboard');
}

function backend_render_admin_contacts(): void
{
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    $page = $page === false || $page === null ? 1 : max(1, min(100000, $page));
    $perPage = 50;
    $offset = ($page - 1) * $perPage;
    $response = backend_supabase_request(
        'GET',
        '/rest/v1/contact_submissions?select=id,name,company,email,phone,service,message,created_at&order=created_at.desc&limit='
            . $perPage . '&offset=' . $offset,
        null,
        true,
        null,
        'count=exact'
    );
    $rows = is_array($response['body']) ? $response['body'] : [];
    $total = count($rows);
    if (isset($response['headers']['content-range']) && preg_match('/\/(\d+)$/', $response['headers']['content-range'], $rangeMatch)) {
        $total = (int) $rangeMatch[1];
    }
    $firstRecord = $rows === [] ? 0 : $offset + 1;
    $lastRecord = $offset + count($rows);
    $content = '<section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-kicker">INBOX</span><h2>Contact enquiries</h2><p>Review the details before removing a record.</p></div><span class="admin-count">' . $firstRecord . '–' . $lastRecord . ' of ' . $total . '</span></div>';
    if ($rows === []) {
        $content .= '<div class="admin-empty"><h3>No enquiries yet</h3><p>New contact form submissions will appear here.</p></div>';
    } else {
        $content .= '<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Contact</th><th>Service</th><th>Message</th><th>Received</th><th><span class="visually-hidden">Actions</span></th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $content .= '<tr><td><strong>' . backend_escape($row['name'] ?? '') . '</strong>';
            if (!empty($row['company'])) {
                $content .= '<small>' . backend_escape($row['company']) . '</small>';
            }
            $content .= '<a href="mailto:' . backend_escape($row['email'] ?? '') . '">' . backend_escape($row['email'] ?? '') . '</a><a href="tel:' . backend_escape(preg_replace('/[^+0-9]/', '', (string) ($row['phone'] ?? ''))) . '">' . backend_escape($row['phone'] ?? '') . '</a></td><td>' . backend_escape(str_replace('-', ' ', (string) ($row['service'] ?? ''))) . '</td><td class="admin-message">' . backend_escape($row['message'] ?? '') . '</td><td>' . backend_escape($row['created_at'] ?? '') . '</td><td><form method="post" data-confirm="Delete this contact enquiry?">'
                . backend_csrf_input() . '<input type="hidden" name="action" value="delete-contact"><input type="hidden" name="id" value="' . backend_escape($row['id'] ?? '') . '"><button class="admin-button admin-button-danger admin-button-small" type="submit">Delete</button></form></td></tr>';
        }
        $content .= '</tbody></table></div>';
    }
    if ($total > $perPage) {
        $content .= '<nav class="admin-pagination" aria-label="Contact enquiry pages">';
        if ($page > 1) {
            $content .= '<a class="admin-button admin-button-secondary admin-button-small" href="/admin/contacts?page=' . ($page - 1) . '">Previous</a>';
        }
        if ($offset + count($rows) < $total) {
            $content .= '<a class="admin-button admin-button-secondary admin-button-small" href="/admin/contacts?page=' . ($page + 1) . '">Next</a>';
        }
        $content .= '</nav>';
    }
    $content .= '</section>';
    backend_admin_shell('Contact enquiries', $content, 'contacts');
}

function backend_render_admin_jobs(): void
{
    $jobs = backend_fetch_jobs(false);
    $editId = (string) ($_GET['edit'] ?? '');
    $editing = null;
    if ($editId !== '') {
        if (!preg_match('/^[0-9a-f-]{36}$/i', $editId)) {
            http_response_code(400);
            throw new InvalidArgumentException('Invalid job identifier.');
        }
        foreach ($jobs as $job) {
            if (strtolower((string) ($job['id'] ?? '')) === strtolower($editId)) {
                $editing = $job;
                break;
            }
        }
        if ($editing === null) {
            http_response_code(404);
            throw new RuntimeException('Job not found.');
        }
    }
    $job = $editing ?? [];
    $content = '<section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-kicker">CAREERS</span><h2>' . ($editing ? 'Edit opportunity' : 'Create an opportunity') . '</h2><p>Draft jobs stay private. Open jobs are published on the public careers page.</p></div></div><form method="post" class="admin-form admin-job-form">'
        . backend_csrf_input() . '<input type="hidden" name="action" value="save-job"><input type="hidden" name="id" value="' . backend_escape($job['id'] ?? '') . '"><label for="job-title">Job title</label><input id="job-title" name="title" maxlength="150" required value="' . backend_escape($job['title'] ?? '') . '"><div class="admin-form-grid"><div><label for="job-location">Location</label><input id="job-location" name="location" maxlength="120" value="' . backend_escape($job['location'] ?? '') . '" placeholder="Maynooth / Hybrid"></div><div><label for="job-type">Employment type</label><input id="job-type" name="employment_type" maxlength="80" value="' . backend_escape($job['employment_type'] ?? '') . '" placeholder="Full-time"></div></div><label for="job-summary">Short summary</label><textarea id="job-summary" name="summary" rows="2" maxlength="500">' . backend_escape($job['summary'] ?? '') . '</textarea><label for="job-description">Job description</label><textarea id="job-description" name="description" rows="6" maxlength="10000">' . backend_escape($job['description'] ?? '') . '</textarea><label for="job-requirements">Requirements <span>one item per line</span></label><textarea id="job-requirements" name="requirements" rows="5" maxlength="10000">' . backend_escape($job['requirements'] ?? '') . '</textarea><label for="job-status">Publication status</label><select id="job-status" name="status"><option value="draft"' . (($job['status'] ?? 'draft') === 'draft' ? ' selected' : '') . '>Draft (private)</option><option value="open"' . (($job['status'] ?? '') === 'open' ? ' selected' : '') . '>Open (publish)</option><option value="closed"' . (($job['status'] ?? '') === 'closed' ? ' selected' : '') . '>Closed</option></select><button class="admin-button" type="submit">' . ($editing ? 'Save changes' : 'Create job') . '</button>';
    if ($editing) {
        $content .= ' <a class="admin-button admin-button-secondary" href="/admin/jobs">Cancel</a>';
    }
    $content .= '</form></section><section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-kicker">JOB BOARD</span><h2>All opportunities</h2></div><span class="admin-count">' . count($jobs) . ' total</span></div>';
    if ($jobs === []) {
        $content .= '<div class="admin-empty"><h3>No jobs created</h3><p>Create a job above to get started.</p></div>';
    } else {
        $content .= '<div class="admin-job-list">';
        foreach ($jobs as $item) {
            $status = (string) ($item['status'] ?? 'draft');
            $content .= '<article class="admin-job-row"><div><span class="admin-status admin-status-' . backend_escape($status) . '">' . backend_escape(ucfirst($status)) . '</span><h3>' . backend_escape($item['title'] ?? '') . '</h3><p>' . backend_escape(trim((string) ($item['location'] ?? '') . ' · ' . (string) ($item['employment_type'] ?? ''), ' ·')) . '</p></div><div class="admin-row-actions"><a class="admin-button admin-button-secondary admin-button-small" href="/admin/jobs?edit=' . rawurlencode((string) ($item['id'] ?? '')) . '">Edit</a><form method="post" data-confirm="Delete this job permanently?">'
                . backend_csrf_input() . '<input type="hidden" name="action" value="delete-job"><input type="hidden" name="id" value="' . backend_escape($item['id'] ?? '') . '"><button class="admin-button admin-button-danger admin-button-small" type="submit">Delete</button></form></div></article>';
        }
        $content .= '</div>';
    }
    $content .= '</section>';
    backend_admin_shell('Careers', $content, 'jobs');
}

function backend_render_admin_admins(array $currentUser): void
{
    $response = backend_supabase_request('GET', '/rest/v1/admin_users?select=user_id,email,is_active,created_at&order=created_at.asc&limit=500', null, true);
    $admins = is_array($response['body']) ? $response['body'] : [];
    $content = '<section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-kicker">ACCESS CONTROL</span><h2>Invite an administrator</h2><p>They will receive a secure email link to set their password. Only invite people who need full admin access.</p></div></div><form method="post" class="admin-form admin-invite-form">'
        . backend_csrf_input() . '<input type="hidden" name="action" value="invite-admin"><label for="admin-email">Work email address</label><div class="admin-inline-form"><input id="admin-email" name="email" type="email" maxlength="254" required placeholder="name@example.com"><button class="admin-button" type="submit">Send invitation</button></div></form></section><section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-kicker">TEAM</span><h2>Administrators</h2></div><span class="admin-count">' . count($admins) . ' active</span></div><div class="admin-admin-list">';
    foreach ($admins as $admin) {
        $isSelf = strtolower((string) ($admin['user_id'] ?? '')) === strtolower((string) $currentUser['id']);
        $content .= '<article class="admin-admin-row"><div class="admin-avatar" aria-hidden="true">' . backend_escape(strtoupper(substr((string) ($admin['email'] ?? '?'), 0, 1))) . '</div><div><strong>' . backend_escape($admin['email'] ?? '') . '</strong><small>Added ' . backend_escape($admin['created_at'] ?? '') . ($isSelf ? ' · You' : '') . '</small></div>';
        if (!$isSelf) {
            $content .= '<form method="post" data-confirm="Remove this administrator? Their Supabase Auth account will also be deleted.">'
                . backend_csrf_input() . '<input type="hidden" name="action" value="remove-admin"><input type="hidden" name="id" value="' . backend_escape($admin['user_id'] ?? '') . '"><button class="admin-button admin-button-danger admin-button-small" type="submit">Remove</button></form>';
        }
        $content .= '</article>';
    }
    $content .= '</div></section>';
    backend_admin_shell('Administrators', $content, 'admins');
}
