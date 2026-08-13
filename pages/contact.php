<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$success = '';
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));
    $token = $_POST['_csrf'] ?? '';

    if(!verify_csrf($token)){
        $error = 'Invalid request.';
    } elseif($name === '' || $email === '' || $message === ''){
        $error = 'Please complete all required fields.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = 'Please provide a valid email address.';
    } else {
        try{
            $stmt = $pdo->prepare('INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $message]);
            $success = 'Thank you — your message has been received.';
            // Try send notification email to site owner if mail() available
            if(function_exists('mail')){
                $to = 'agneskapinga@gmail.com';
                $subject = 'New contact message from ' . $name;
                $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
                $headers = 'From: ' . $email . "\r\n" . 'Reply-To: ' . $email . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                @mail($to, $subject, $body, $headers);
            }
        } catch(Exception $e){
            $error = 'Unable to submit your message at this time.';
        }
    }
}

?>
<main class="page">
  <div class="container">
    <header class="page-header">
      <h2 class="section-title">Contact</h2>
      <p class="section-sub">Get in touch — send a message and I will respond.</p>
    </header>

    <section class="section">
      <?php if($success): ?><div class="notice success"><?php echo esc($success); ?></div><?php endif; ?>
      <?php if($error): ?><div class="notice error"><?php echo esc($error); ?></div><?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">
        <form method="post" action="/pages/contact.php" style="max-width:640px">
          <?php echo csrf_field(); ?>
          <label style="display:block;margin-bottom:8px;color:var(--text-secondary)">Your name *
            <input name="name" required value="<?php echo esc($_POST['name'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)">
          </label>
          <label style="display:block;margin-bottom:8px;color:var(--text-secondary)">Email *
            <input name="email" type="email" required value="<?php echo esc($_POST['email'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)">
          </label>
          <label style="display:block;margin-bottom:8px;color:var(--text-secondary)">Phone
            <input name="phone" value="<?php echo esc($_POST['phone'] ?? ''); ?>" style="width:100%;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)">
          </label>
          <label style="display:block;margin-bottom:8px;color:var(--text-secondary)">Message *
            <textarea name="message" required style="width:100%;min-height:140px;padding:10px;margin-top:6px;border-radius:8px;border:1px solid var(--border);background:transparent;color:var(--text-primary)"><?php echo esc($_POST['message'] ?? ''); ?></textarea>
          </label>
          <div style="margin-top:12px">
            <button class="btn btn-primary" type="submit">Send Message</button>
          </div>
        </form>

        <aside style="background:var(--bg-secondary);padding:18px;border-radius:12px;">
          <h4 style="margin-top:0">Contact Details</h4>
          <p><strong>Phone:</strong> 34567654345</p>
          <p><strong>Email:</strong> <a href="mailto:agneskapinga@gmail.com">agneskapinga@gmail.com</a></p>
          <hr style="border-color:rgba(255,255,255,0.03)">
          <p style="color:var(--text-secondary)">You can send a private message using the form. Required fields are name, email and message. Messages are delivered to site administrators.</p>
        </aside>
      </div>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
