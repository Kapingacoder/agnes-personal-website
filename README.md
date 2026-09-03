# Agnes Personal Website — MySQL setup

This project is configured to use MySQL by default.

1) Create a MySQL database:

```bash
mysql -u root -p
```

Then, inside MySQL:

```sql
CREATE DATABASE agnes_personal_website;
EXIT;
```

2) Import the schema and seed admin record:

```bash
mysql -u root -p agnes_personal_website < database/database.sql
```

3) The app defaults are configured as:

- driver: `mysql`
- host: `127.0.0.1`
- port: `3306`
- dbname: `agnes_personal_website`
- user: `root`
- pass: `KApinga003`

The admin login is:

- username: `agnes`
- password: `KApinga003`

Security note: do not commit production credentials. Use environment variables in production deployments.
