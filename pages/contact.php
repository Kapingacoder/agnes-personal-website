<?php
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
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

<main class="page content-page content-page--contact">
  <div class="container">
    <header class="page-hero page-hero--inner">
      <span class="page-hero__eyebrow">Get in Touch</span>
      <h2 class="page-hero__title">Contact &amp; Messages</h2>
      <p class="page-hero__text">Send a message through a smoother, more polished contact experience designed for both mobile and desktop users.</p>
    </header>

    <section class="contact-layout">
      <div class="contact-panel contact-panel--form">
        <?php if($success): ?><div class="notice notice-success"><span><?php echo esc($success); ?></span></div><?php endif; ?>
        <?php if($error): ?><div class="notice notice-error"><span><?php echo esc($error); ?></span></div><?php endif; ?>

        <div class="contact-panel__intro">
          <span class="content-card__tag">Message Form</span>
          <h3>Start a conversation</h3>
          <p>Provide the information you want to share. Required fields are clearly marked, and your message goes directly to the site administrator.</p>
        </div>

        <form method="post" action="/pages/contact.php" class="contact-form">
          <?php echo csrf_field(); ?>
          <div class="contact-form__grid">
            <label class="contact-field">
              <span>Your name *</span>
              <input name="name" required value="<?php echo esc($_POST['name'] ?? ''); ?>" placeholder="Enter your full name">
            </label>
            <label class="contact-field">
              <span>Email *</span>
              <input name="email" type="email" required value="<?php echo esc($_POST['email'] ?? ''); ?>" placeholder="your.email@example.com">
            </label>
            <label class="contact-field contact-field--full">
              <span>Phone</span>
              <input name="phone" value="<?php echo esc($_POST['phone'] ?? ''); ?>" placeholder="+255 123 456 789">
            </label>
            <label class="contact-field contact-field--full">
              <span>Message *</span>
              <textarea name="message" required placeholder="Write your message here..."><?php echo esc($_POST['message'] ?? ''); ?></textarea>
            </label>
          </div>
          <div class="contact-form__actions">
            <button class="btn btn-primary" type="submit">Send Message</button>
          </div>
        </form>
      </div>

      <aside class="contact-panel contact-panel--info">
        <span class="content-card__tag">Contact Details</span>
        <h3>Reach out directly</h3>
        <p class="contact-panel__lead">For academic collaboration, consultancy, or other professional enquiries, use the details below or submit the form.</p>

        <div class="contact-info-list">
          <div class="detail-chip detail-chip--phone">
            <span class="detail-chip__icon">📞</span>
            <div class="detail-chip__content">
              <span>Phone</span>
              <strong><a href="tel:+255683794973">+255 683 794 973</a></strong>
            </div>
          </div>
          <div class="detail-chip detail-chip--email">
            <span class="detail-chip__icon">✉️</span>
            <div class="detail-chip__content">
              <span>Email</span>
              <strong><a href="mailto:agnesgisbertkapinga@gmail.com">agnesgisbertkapinga@gmail.com</a></strong>
            </div>
          </div>
        </div>

        <div class="contact-side-note">
          <h4>Response Time</h4>
          <p>Messages are typically reviewed within 24-48 hours during business days. For urgent matters, please use the phone contact provided above.</p>
        </div>

        <div class="contact-social-links">
          <h4>Connect</h4>
          <div class="social-links">
            <a href="https://www.facebook.com/agnes.kapinga.7/" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Facebook">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.07C22 6.48 17.52 2 11.93 2S1.86 6.48 1.86 12.07c0 4.99 3.66 9.13 8.44 9.92v-7.02H8.08v-2.9h2.22V9.41c0-2.2 1.3-3.42 3.28-3.42.95 0 1.95.17 1.95.17v2.14h-1.1c-1.09 0-1.43.68-1.43 1.38v1.65h2.43l-.39 2.9h-2.04V22c4.78-.79 8.44-4.93 8.44-9.93z"/></svg>
            </a>
            <a href="https://www.instagram.com/agygisy?stkn=dncxZ3hhZGNlemRp&amp;utm_source=qr" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="Instagram">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 6.5A4.5 4.5 0 1016.5 13 4.5 4.5 0 0012 8.5zm6.5-3.8a1.2 1.2 0 11-1.2 1.2 1.2 1.2 0 011.2-1.2z"/></svg>
            </a>
            <a href="https://www.linkedin.com/in/agnes-gisbert-kapinga-2b38a2241?utm_source=share_via&amp;utm_content=profile&amp;utm_medium=member_ios" target="_blank" rel="noopener noreferrer" class="social-link" aria-label="LinkedIn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
          </div>
        </div>

        <div class="contact-side-note">
          <p>Messages sent here are stored in the admin dashboard inbox, where the administrator can review them without losing previously received content.</p>
        </div>
      </aside>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php';
