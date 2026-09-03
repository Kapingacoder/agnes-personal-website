# Agnes Personal Website - Hosting Documentation

## Overview
This document provides comprehensive information for hosting the Agnes Personal Website, a PHP-based content management system with MySQL database backend. This guide is intended for both hosting providers and the site owner to ensure successful deployment and maintenance.

---

## 1. System Requirements

### Server Requirements
- **Web Server**: Apache HTTP Server (recommended) or Nginx
- **PHP Version**: PHP 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.3+
- **Operating System**: Linux (Ubuntu, CentOS, Debian recommended)

### Required PHP Extensions
- PDO
- PDO_MySQL
- mbstring
- fileinfo
- json
- session
- openssl
- gd (for image processing, optional but recommended)

### Resource Requirements
- **Minimum Disk Space**: 500MB (excluding uploads)
- **Recommended Disk Space**: 2GB+ (including uploads)
- **Minimum RAM**: 512MB
- **Recommended RAM**: 1GB+

---

## 2. Database Configuration

### Database Specifications
- **Database Name**: `agnes_personal_website` (or custom name)
- **Database Charset**: UTF-8MB4
- **Database Collation**: utf8mb4_unicode_ci

### Database User Permissions
The database user should have the following permissions:
- SELECT, INSERT, UPDATE, DELETE
- CREATE, ALTER, INDEX (for migrations)
- DROP (recommended for development/testing)

### Database Schema
The application requires the following tables:
- `admins` - Administrator accounts
- `lecturers` - Lecturer profiles and information
- `publications` - Academic publications
- `projects` - Project journals and documents
- `consultancy_videos` - Consultancy video content
- `contacts` - Contact form submissions

### Default Admin Credentials
- **Username**: `agnes`
- **Password**: `KApinga003` (MUST be changed immediately after deployment)

---

## 3. File Structure

### Directory Structure
```
agnes-personal-website/
├── admin/              # Admin panel files
├── assets/            # Static assets (CSS, JS, images)
├── config/            # Configuration files
├── database/          # Database schema file
├── includes/          # Shared PHP files
├── pages/             # Public pages
├── uploads/           # User-uploaded files (needs write permissions)
├── .env               # Environment variables (DO NOT commit to version control)
├── .env.example       # Environment variables template
├── .htaccess          # Apache configuration
├── .user.ini          # PHP configuration
├── index.php          # Main entry point
└── README.md          # Project documentation
```

### File Permissions
- **Uploads directory**: 755 (drwxr-xr-x) or 777 (drwxrwxrwx) if needed
- **All other directories**: 755 (drwxr-xr-x)
- **PHP files**: 644 (rw-r--r--)
- **Configuration files**: 644 (rw-r--r--) or 600 (rw-------) for sensitive files

---

## 4. Environment Variables

### Required Environment Variables
Create a `.env` file in the project root with the following variables:

```bash
# Database Configuration
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=agnes_personal_website
DB_USER=your_database_user
DB_PASS=your_secure_password
DB_CHARSET=utf8mb4

# Application Environment
APP_ENV=production
```

### Security Notes
- **NEVER** commit `.env` file to version control
- Use strong, unique passwords for database credentials
- Change default admin password immediately
- Keep `.env` file outside web root if possible

---

## 5. Hosting Provider Requirements

### What the Hosting Provider Needs to Know

#### Server Configuration
1. **Web Server Setup**:
   - Apache with mod_rewrite enabled
   - Proper .htaccess support
   - Document root pointing to project directory

2. **PHP Configuration**:
   - PHP 8.0+ installed
   - Required extensions enabled (PDO, PDO_MySQL, mbstring, fileinfo)
   - Recommended PHP settings in php.ini:
     ```ini
     upload_max_filesize = 10M
     post_max_size = 10M
     max_execution_time = 300
     memory_limit = 256M
     ```

3. **Database Setup**:
   - MySQL/MariaDB server
   - Create database with UTF-8MB4 charset
   - Create database user with appropriate permissions
   - Provide database credentials for .env configuration

4. **SSL Certificate**:
   - SSL/TLS certificate for HTTPS
   - Force HTTPS redirects
   - Secure headers implementation

#### Security Measures
1. **File Permissions**:
   - Proper permission settings for directories and files
   - Restrict access to sensitive files (.env, config files)
   - Disable directory listing

2. **Firewall Configuration**:
   - Only allow necessary ports (80, 443, SSH if needed)
   - Database access restricted to localhost only

3. **Backup Strategy**:
   - Regular database backups (daily recommended)
   - File system backups (including uploads)
   - Backup retention policy

4. **Monitoring**:
   - Server uptime monitoring
   - Disk space monitoring
   - Error log monitoring
   - Security scanning

---

## 6. Deployment Steps

### Step 1: Prepare the Server
1. Set up web server (Apache/Nginx)
2. Install PHP 8.0+ with required extensions
3. Install MySQL/MariaDB
4. Configure SSL certificate

### Step 2: Upload Files
1. Upload all project files to server
2. Set proper file permissions
3. Ensure uploads directory is writable

### Step 3: Database Setup
1. Create MySQL database:
   ```sql
   CREATE DATABASE agnes_personal_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Create database user:
   ```sql
   CREATE USER 'agnes_user'@'localhost' IDENTIFIED BY 'secure_password';
   GRANT ALL PRIVILEGES ON agnes_personal_website.* TO 'agnes_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Import database schema:
   ```bash
   mysql -u agnes_user -p agnes_personal_website < database/database.sql
   ```

### Step 4: Configure Environment
1. Copy `.env.example` to `.env`
2. Update database credentials in `.env`
3. Set `APP_ENV=production`

### Step 5: Test the Application
1. Access the website via HTTPS
2. Test all public pages
3. Access admin panel (`/admin/`)
4. Change default admin password
5. Test file upload functionality
6. Test database operations

---

## 7. Things to Consider for the Site Owner

### Security Considerations
1. **Change Default Credentials**:
   - Immediately change the default admin password
   - Use strong, unique passwords

2. **Regular Updates**:
   - Keep PHP and MySQL updated
   - Update the application code regularly
   - Monitor security advisories

3. **Access Control**:
   - Limit admin panel access to trusted IPs if possible
   - Use strong authentication
   - Implement 2FA if available

### Performance Optimization
1. **Caching**:
   - Enable browser caching for static assets
   - Consider implementing server-side caching
   - Use CDN for static assets if possible

2. **Image Optimization**:
   - Compress images before upload
   - Use appropriate image formats
   - Implement lazy loading

3. **Database Optimization**:
   - Regular database maintenance
   - Index optimization
   - Query optimization

### Backup Strategy
1. **Database Backups**:
   - Daily automated backups
   - Weekly full backups
   - Store backups off-site
   - Test restore procedures

2. **File Backups**:
   - Regular backups of uploaded files
   - Version control for application code
   - Configuration file backups

### Monitoring and Maintenance
1. **Uptime Monitoring**:
   - Use services like UptimeRobot or Pingdom
   - Set up alert notifications

2. **Error Logging**:
   - Monitor PHP error logs
   - Monitor database error logs
   - Set up log rotation

3. **Performance Monitoring**:
   - Monitor page load times
   - Monitor server resource usage
   - Set up performance alerts

### SSL and HTTPS
1. **SSL Certificate**:
   - Use valid SSL certificate
   - Enable HSTS
   - Implement secure headers

2. **HTTPS Redirects**:
   - Force all traffic to HTTPS
   - Update internal links to use HTTPS

### Content Management
1. **Regular Content Updates**:
   - Keep publications and projects updated
   - Review and remove outdated content
   - Regular blog updates if applicable

2. **File Management**:
   - Regular cleanup of unused uploads
   - Monitor disk space usage
   - Organize uploads properly

---

## 8. Troubleshooting Common Issues

### Database Connection Issues
- **Problem**: Cannot connect to database
- **Solution**: Check database credentials in `.env`, ensure MySQL is running, verify database exists

### File Upload Issues
- **Problem**: Files not uploading
- **Solution**: Check PHP upload limits, verify directory permissions, check disk space

### 500 Internal Server Error
- **Problem**: Server error pages
- **Solution**: Check error logs, verify file permissions, ensure .htaccess is correct

### Admin Panel Access Issues
- **Problem**: Cannot access admin panel
- **Solution**: Check authentication, verify session configuration, clear browser cookies

---

## 9. Post-Deployment Checklist

### Immediate Actions
- [ ] Change default admin password
- [ ] Verify all pages are loading correctly
- [ ] Test file upload functionality
- [ ] Test admin panel functionality
- [ ] Verify SSL certificate is working
- [ ] Set up database backups
- [ ] Configure error logging

### Ongoing Maintenance
- [ ] Regular security updates
- [ ] Monitor server resources
- [ ] Review access logs
- [ ] Test backup restore procedures
- [ ] Update content regularly
- [ ] Monitor uptime and performance

---

## 10. Contact Information

### For Hosting Provider
- **Technical Support**: [Hosting Provider Contact]
- **Emergency Contact**: [Emergency Contact Information]
- **Support Hours**: [Support Hours]

### For Site Owner
- **Admin Panel URL**: `https://yourdomain.com/admin/`
- **Admin Username**: [Your Admin Username]
- **Admin Password**: [Your Admin Password - STORE SECURELY]

---

## 11. Additional Resources

### Documentation
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- Apache Documentation: https://httpd.apache.org/docs/

### Security Resources
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security Guide: https://php.net/manual/en/security.php

### Performance Optimization
- WebPageTest: https://www.webpagetest.org/
- Google PageSpeed Insights: https://pagespeed.web.dev/

---

## 12. Version Information

- **Application Version**: 1.0
- **Last Updated**: September 2, 2026
- **Database Schema Version**: MySQL Compatible
- **PHP Minimum Version**: 8.0

---

## Important Notes

1. **Security First**: Always prioritize security in all configurations and deployments
2. **Backups**: Maintain regular, tested backups of both database and files
3. **Monitoring**: Implement comprehensive monitoring to catch issues early
4. **Updates**: Keep all components updated for security and performance
5. **Testing**: Thoroughly test all functionality after any changes or updates

---

This documentation should be reviewed and updated regularly to reflect any changes in the application or hosting environment.
