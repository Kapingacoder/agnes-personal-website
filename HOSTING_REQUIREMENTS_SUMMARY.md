# Hosting Requirements Summary - Agnes Personal Website

## Quick Overview
PHP-based personal website with MySQL database requiring standard LAMP/LEMP stack with specific configuration.

---

## CRITICAL Requirements

### Server Specifications
- **PHP**: 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Web Server**: Apache (preferred) or Nginx
- **SSL/TLS**: Required (HTTPS only)

### Required PHP Extensions
```
PDO, PDO_MySQL, mbstring, fileinfo, json, session, openssl
```

### PHP Configuration (php.ini)
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

---

## Database Requirements

### Database Setup
- **Database Name**: `agnes_personal_website` (or custom)
- **Charset**: UTF-8MB4
- **Collation**: utf8mb4_unicode_ci

### Tables Required
- admins, lecturers, publications, projects, consultancy_videos, contacts

### Database User Permissions
SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP

---

## File System Requirements

### Directory Permissions
- **Uploads directory**: 755 or 777 (must be writable)
- **Other directories**: 755
- **PHP files**: 644

### Directory Structure
```
project_root/
├── admin/          # Admin panel
├── assets/         # CSS, JS, images
├── config/         # Configuration files
├── database/       # SQL schema file
├── includes/       # PHP includes
├── pages/          # Public pages
├── uploads/        # User uploads (WRITABLE)
├── .env            # Environment config (CREATE THIS)
└── index.php       # Entry point
```

---

## Security Requirements

### MUST Implement
1. **SSL Certificate**: Valid SSL/TLS certificate required
2. **HTTPS Only**: Force all traffic to HTTPS
3. **File Permissions**: Restrict access to sensitive files
4. **Database Security**: Localhost access only for database
5. **Firewall**: Only allow ports 80, 443 (and SSH if needed)

### Environment Variables
Create `.env` file with:
```bash
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=agnes_personal_website
DB_USER=database_user
DB_PASS=secure_password
DB_CHARSET=utf8mb4
APP_ENV=production
```

---

## Deployment Steps

### 1. Server Setup
- Install web server (Apache with mod_rewrite)
- Install PHP 8.0+ with required extensions
- Install MySQL/MariaDB
- Configure SSL certificate

### 2. Database Setup
```sql
CREATE DATABASE agnes_personal_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'agnes_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON agnes_personal_website.* TO 'agnes_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Import Schema
```bash
mysql -u agnes_user -p agnes_personal_website < database/database.sql
```

### 4. Application Setup
- Upload all files to server
- Create `.env` file with production credentials
- Set proper file permissions
- Ensure uploads directory is writable

### 5. Testing
- Access site via HTTPS
- Test all pages
- Access admin panel at `/admin/`
- Change default admin password

---

## Important Notes

### Default Admin Credentials
- **Username**: `agnes`
- **Password**: `KApinga003`
- **ACTION REQUIRED**: Change immediately after deployment

### Backup Requirements
- **Database**: Daily automated backups
- **Files**: Regular backups including uploads
- **Off-site storage**: Required for disaster recovery

### Monitoring
- Server uptime monitoring
- Disk space monitoring
- Error log monitoring
- Security scanning

---

## Contact & Support

### For Hosting Provider
- Document version: 1.0
- Last updated: September 2, 2026
- Database schema: MySQL compatible

### For Site Owner
- **Admin Panel**: `https://yourdomain.com/admin/`
- **Change default password immediately**
- **Keep database credentials secure**
- **Regular content updates recommended**

---

## Resource Requirements

### Minimum
- **Disk Space**: 500MB (excluding uploads)
- **RAM**: 512MB
- **CPU**: 1 core

### Recommended
- **Disk Space**: 2GB+ (including uploads)
- **RAM**: 1GB+
- **CPU**: 2+ cores

---

## Troubleshooting Quick Reference

| Issue | Solution |
|-------|----------|
| Database connection failed | Check `.env` credentials, verify MySQL running |
| File uploads not working | Check PHP upload limits, directory permissions |
| 500 Internal Server Error | Check error logs, file permissions |
| Admin panel inaccessible | Check authentication, session configuration |

---

## Additional Documentation
- Full hosting documentation: `HOSTING_DOCUMENTATION.md`
- Deployment checklist: `DEPLOYMENT_CHECKLIST.md`
- Project README: `README.md`

---

**IMPORTANT**: This is a PHP application that requires proper server configuration. Ensure all requirements are met before deployment.
