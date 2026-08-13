<?php
require_once __DIR__ . '/../includes/auth.php';
require_auth();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

// Fetch counts for overview
$counts = [];
$counts['lecturers'] = (int)$pdo->query('SELECT COUNT(*) FROM lecturers')->fetchColumn();
$counts['publications'] = (int)$pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
$counts['videos'] = (int)$pdo->query('SELECT COUNT(*) FROM consultancy_videos')->fetchColumn();
$counts['messages'] = 0;
try{
    $counts['messages'] = (int)$pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
}catch(Exception $e){
    $counts['messages'] = 0;
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <style>
    .admin-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px}
    .admin-card{background:var(--bg-secondary);padding:18px;border-radius:10px}
    @media (max-width:900px){.admin-grid{grid-template-columns:1fr}}
  </style>
</head>
<body class="bg-primary">
  <?php require __DIR__ . '/../includes/navbar.php'; ?>
  <main class="page">
    <div class="container">
      <header class="page-header">
        <h2 class="section-title">Admin Dashboard</h2>
        <p class="section-sub">Overview</p>
      </header>

      <section class="section admin-grid">
        <div class="admin-card">
          <h4>Lecturers</h4>
          <p style="font-size:28px"><?php echo (int)$counts['lecturers']; ?></p>
          <p><a href="/admin/lecturer/add.php" class="btn btn-primary">Add Lecturer</a></p>
        </div>

        <div class="admin-card">
          <h4>Publications</h4>
          <p style="font-size:28px"><?php echo (int)$counts['publications']; ?></p>
          <p><a href="/admin/publications/add.php" class="btn btn-primary">Add Publication</a></p>
        </div>

        <div class="admin-card">
          <h4>Consultancy Videos</h4>
          <p style="font-size:28px"><?php echo (int)$counts['videos']; ?></p>
          <p><a href="/admin/consultancy/add.php" class="btn btn-primary">Add Video</a></p>
        </div>

        <div class="admin-card">
          <h4>Contact Messages</h4>
          <p style="font-size:28px"><?php echo (int)$counts['messages']; ?></p>
          <p><a href="/admin/index.php" class="btn btn-secondary">View Messages</a></p>
        </div>
      </section>

      <section class="section">
        <a href="/admin/logout.php" class="btn btn-secondary">Logout</a>
      </section>
    </div>
  </main>

  <?php require __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
<?php
// Admin dashboard placeholder
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body>
<h1>Admin Dashboard</h1>
<p>Admin controls go here.</p>
</body>
</html>
