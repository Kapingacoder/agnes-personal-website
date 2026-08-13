# Agnes Personal Website — MySQL setup

This README explains how to create the MySQL database and user for the `agnes-personal-website` project.

1) Open a terminal and connect to MySQL as a user with privileges to create databases and users (for example `root`):

```bash
mysql -u root -p
```

2) Run the following SQL commands (you can also import `database/database.sql`):

```sql
-- create database and user (change password)
CREATE DATABASE IF NOT EXISTS `agnes_personal_website` DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
CREATE USER 'agnes'@'localhost' IDENTIFIED BY 'change_this_password';
GRANT ALL PRIVILEGES ON `agnes_personal_website`.* TO 'agnes'@'localhost';
FLUSH PRIVILEGES;

-- import schema from file (run from shell):
-- mysql -u agnes -p agnes_personal_website < database/database.sql
```

3) Update `config/database.php` if you changed the user or password. The default config uses:

- host: `127.0.0.1`
- dbname: `agnes_personal_website`
- user: `agnes`
- pass: `change_this_password`

Security note: do not commit production credentials. Use environment variables in production deployments.
