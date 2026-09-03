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
            <a href="#" class="social-link" aria-label="LinkedIn">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="#" class="social-link" aria-label="Twitter">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
            </a>
            <a href="#" class="social-link" aria-label="ResearchGate">
              <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zm-1.5 17.5c-3.037 0-5.5-2.463-5.5-5.5s2.463-5.5 5.5-5.5 5.5 2.463 5.5 5.5-2.463 5.5-5.5 5.5zm1.5-5.5c0 .828-.672 1.5-1.5 1.5s-1.5-.672-1.5-1.5.672-1.5 1.5-1.5 1.5.672 1.5 1.5z"/></svg>
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
