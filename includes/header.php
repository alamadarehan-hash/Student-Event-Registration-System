<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$base_url = "http://" . $_SERVER['SERVER_NAME'] . "/student-system/";
$current_page = basename($_SERVER['PHP_SELF']);
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? '';

/* FETCH USER PROFILE & NOTIFICATIONS */
$profileUser = null;
$notification_events = [];
$unread_count = 0;

if ($is_logged_in) {
    // 1. Fetch profile picture
    $profileStmt = $pdo->prepare("
        SELECT profile_pic 
        FROM users 
        WHERE id = ?
    ");
    $profileStmt->execute([$_SESSION['user_id']]);
    $profileUser = $profileStmt->fetch();

    // 2. Fetch upcoming and ongoing events for notifications
    // This looks for events happening today or in the future
    $notiStmt = $pdo->prepare("
        SELECT id, title, event_date, category 
        FROM events 
        WHERE event_date >= CAST(NOW() AS DATE)
        ORDER BY event_date ASC 
        LIMIT 5
    ");
    $notiStmt->execute();
    $notification_events = $notiStmt->fetchAll();
    $unread_count = count($notification_events);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Event Registration System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 240px;
            --top-nav-bg: #0b2240;
            --admin-sidebar-bg: #1e293b;
            --sys-blue: #0d6efd;
            --sys-bg: #f0f2f5;
            --sys-card-radius: 16px;
            --sys-input-radius: 8px;
        }

        body {
            background-color: var(--sys-bg);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            margin: 0;
        }

        /* AUTH & CARDS */
        .auth-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .sys-card {
            background: #ffffff;
            border: none;
            border-radius: var(--sys-card-radius);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        }

        .sys-icon-banner {
            color: var(--sys-blue);
            font-size: 4rem;
            line-height: 1;
        }

        .form-label {
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 6px;
        }

        .form-control, .form-select {
            border-radius: var(--sys-input-radius);
            padding: 10px 14px;
            border: 1px solid #dee2e6;
        }

        .input-group-text-sys {
            background: #ffffff;
            border-left: none;
            cursor: pointer;
            color: #6c757d;
            border-top-right-radius: var(--sys-input-radius)!important;
            border-bottom-right-radius: var(--sys-input-radius)!important;
        }

        .input-has-toggle { border-right: none; }

        .btn-sys-primary {
            background-color: var(--sys-blue);
            border: none;
            border-radius: var(--sys-input-radius);
            padding: 12px;
            font-weight: 600;
            color: #ffffff;
        }

        .btn-sys-primary:hover {
            background-color: #0b5ed7;
            color: #ffffff;
        }

        /* TOP HEADER */
        .system-top-header {
            background-color: var(--top-nav-bg);
            color: #ffffff;
            height: 60px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }

        .header-brand-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }

        /* SIDEBAR */
        #sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 60px);
            position: fixed;
            left: 0;
            top: 60px;
            z-index: 1020;
            padding-top: 15px;
            background-color: #ffffff;
            border-right: 1px solid #e9ecef;
        }

        .sidebar-admin-theme {
            background-color: var(--admin-sidebar-bg) !important;
            border-right: none !important;
        }

        .sidebar-nav .nav-link {
            padding: 12px 24px;
            display: flex;
            align-items: center;
            font-weight: 500;
            text-decoration: none;
            border-radius: 0 8px 8px 0;
            margin-right: 15px;
            transition: all 0.2s ease;
            color: #495057;
        }

        .sidebar-admin-theme .nav-link { color: #cbd5e1; }

        .sidebar-nav .nav-link:hover {
            color: var(--sys-blue);
            background-color: #f0f4f8;
        }

        .sidebar-admin-theme .nav-link:hover {
            color: #ffffff;
            background-color: rgba(255,255,255,0.05);
        }

        .sidebar-nav .nav-link.active {
            color: var(--sys-blue);
            background-color: #e6f0ff;
            font-weight: 600;
        }

        .sidebar-admin-theme .nav-link.active {
            color: #ffffff;
            background-color: var(--sys-blue);
        }

        .sidebar-nav .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .sidebar-nav .logout-link { color: #dc3545 !important; }
        .sidebar-nav .logout-link:hover { background-color: #fff5f5; }
        .sidebar-admin-theme .logout-link:hover { background-color: rgba(220, 53, 69, 0.1); }

        /* MAIN CONTENT */
        #main-content-layout {
            padding-top: 85px;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-right: 24px;
            padding-left: 24px;
            background-color: #f8f9fa;
        }

        .auth-layout-active {
            margin-left: 0 !important;
            padding-top: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            background-color: var(--sys-bg) !important;
        }

        /* ADMIN STYLE */
        .admin-sys-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .table th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px;
            border-bottom: 2px solid #e2e8f0;
        }

        .table td {
            padding: 14px;
            vertical-align: middle;
            color: #334155;
            font-size: 0.95rem;
        }

        .badge-admin-pill {
            background-color: rgba(13, 110, 253, 0.15);
            color: #0d6efd;
            font-size: 0.7rem;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* CUSTOM NOTIFICATION STYLES */
        .notification-dropdown {
            width: 320px;
            max-height: 420px;
            overflow-y: auto;
        }
        .notification-item {
            transition: background 0.2s ease;
            white-space: normal !important;
        }
        .notification-item:hover {
            background-color: #f8fafc;
        }

    </style>
</head>

<body>

<?php if ($is_logged_in): ?>

<header class="system-top-header">

    <div class="d-flex align-items-center">
        <a href="#" class="header-brand-link">
            <i class="bi bi-bank2 me-2"></i>
            <span>Student Event System</span>
            <?php if ($user_role === 'admin'): ?>
                <span class="badge-admin-pill ms-2">Admin</span>
            <?php endif; ?>
        </a>
    </div>

    <div class="d-flex align-items-center gap-3">

        <div class="dropdown">
            <div class="position-relative me-2" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBell">
                <i class="bi bi-bell fs-5" style="cursor: pointer;"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; padding: 0.35em 0.5em;">
                        <?= $unread_count ?>
                    </span>
                <?php endif; ?>
            </div>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 notification-dropdown">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">Upcoming & Ongoing Events</div>
                </li>
                
                <?php if ($unread_count > 0): ?>
                    <?php foreach ($notification_events as $noti_event): ?>
                        <li>
                            <a class="dropdown-item py-2.5 px-3 notification-item border-bottom d-block" 
                               href="<?= $base_url ?><?= $user_role === 'admin' ? 'admin/events.php' : 'student/dashboard.php' ?>">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($noti_event['category'] ?? 'General') ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <?= date('M d', strtotime($noti_event['event_date'])) ?>
                                    </small>
                                </div>
                                <div class="fw-semibold text-truncate text-dark" style="font-size: 0.88rem;">
                                    <?= htmlspecialchars($noti_event['title']) ?>
                                </div>
                                <small class="text-secondary" style="font-size: 0.75rem;">
                                    <?php 
                                        $e_date = date('Y-m-d', strtotime($noti_event['event_date']));
                                        echo ($e_date == date('Y-m-d')) ? 'Happening Today!' : 'Upcoming Event';
                                    ?>
                                </small>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center py-4 text-muted">
                        <i class="bi bi-calendar-x fs-4 d-block mb-1 text-black-50"></i>
                        <span class="small">No active events found.</span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="text-end d-none d-sm-block">
            <div class="fw-bold lh-1" style="font-size: 0.9rem;">
                <?= htmlspecialchars($_SESSION['user_name'] ?? 'System User') ?>
            </div>
            <small class="text-white-50 text-capitalize" style="font-size: 0.75rem;">
                <?= htmlspecialchars($user_role) ?>
            </small>
        </div>

        <div class="dropdown">
            <div class="d-flex align-items-center justify-content-center"
                 role="button"
                 data-bs-toggle="dropdown"
                 aria-expanded="false">

                <?php
                $profilePic = $profileUser['profile_pic'] ?? '';
                $imageUrl = $base_url . "uploads/profile/" . $profilePic;
                $serverPath = $_SERVER['DOCUMENT_ROOT'] . "/student-system/uploads/profile/" . $profilePic;
                ?>

                <?php if (!empty($profilePic) && file_exists($serverPath)): ?>
                    <img src="<?= htmlspecialchars($imageUrl) ?>"
                        class="rounded-circle border border-2 border-light shadow-sm"
                        style="width:40px;height:40px;object-fit:cover;cursor:pointer;">
                <?php else: ?>
                    <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width:40px;height:40px;cursor:pointer;">
                        <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                <li class="px-3 py-2">
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                    <small class="text-muted text-capitalize"><?= htmlspecialchars($user_role) ?></small>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php if ($user_role === 'student'): ?>
                <li>
                    <a class="dropdown-item py-2" href="<?= $base_url ?>student/profile.php">
                        <i class="bi bi-person-circle me-2"></i>Profile Settings
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?= $base_url ?>logout.php">
                        <i class="bi bi-box-arrow-left me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>

    </div>

</header>

<aside id="sidebar" class="<?= $user_role === 'admin' ? 'sidebar-admin-theme' : '' ?>">
    <nav class="nav flex-column sidebar-nav">
        <?php if ($user_role === 'admin'): ?>
            <a href="<?= $base_url ?>admin/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>Dashboard
            </a>
            <a href="<?= $base_url ?>admin/events.php" class="nav-link <?= strpos($current_page, 'events.php') !== false ? 'active' : '' ?>">
                <i class="bi bi-calendar-event-fill"></i>Events
            </a>
            <a href="<?= $base_url ?>admin/registrants.php" class="nav-link <?= $current_page == 'registrants.php' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>Registrants
            </a>
            <a href="<?= $base_url ?>admin/users.php" class="nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
                <i class="bi bi-person-gear"></i>Users
            </a>
        <?php else: ?>
            <a href="<?= $base_url ?>student/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-house-door-fill"></i>Dashboard
            </a>
            <a href="<?= $base_url ?>student/my-events.php" class="nav-link <?= $current_page == 'my-events.php' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check-fill"></i>My Events
            </a>
            <a href="<?= $base_url ?>student/profile.php" class="nav-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="bi bi-person-circle"></i>Profile
            </a>
        <?php endif; ?>

        <a href="<?= $base_url ?>logout.php" class="nav-link logout-link mt-5 border-top pt-3">
            <i class="bi bi-box-arrow-left"></i>Logout
        </a>
    </nav>
</aside>

<?php endif; ?>

<div id="main-content-layout" class="<?= !$is_logged_in ? 'auth-layout-active' : '' ?>">