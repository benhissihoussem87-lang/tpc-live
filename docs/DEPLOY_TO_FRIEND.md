TPC – Friend Deployment Guide

Use this guide to set up the app on another Windows PC with XAMPP and apply the database adjustments you listed. Share this file along with the project folder.

Prerequisites
- XAMPP installed (Apache + MySQL/MariaDB) – same major PHP version as yours.
- Project folder placed at C:\xampp\htdocs\tpc (default). If you use a different folder name, adjust the paths below and in .htaccess.

1) Copy the App
- Copy your entire `tpc` folder into `C:\xampp\htdocs\tpc` on the target PC.
- Start XAMPP Control Panel → Start Apache and MySQL.

2) Configure DB Connection
- Open `class/connexion.db.php` and set DB credentials for the target PC. Typical XAMPP defaults:
  - host: `localhost`
  - port: `3306`
  - user: `root`
  - password: (empty)
  - dbname: your DB name (e.g., `tpc`)

3) Import/Sync Database
- If the target already has the same DB contents, skip to step 4.
- Otherwise, in phpMyAdmin (http://localhost/phpmyadmin):
  - Create a DB named `tpc` (or your chosen DB name).
  - Import your full export (`.sql`) from your machine.

4) Apply the DB Patch (your changes)
Paste the SQL from `db/sql/db_patch_invoice_keys.sql` in phpMyAdmin’s SQL tab, run it section by section. If you see “duplicate key” or “can’t drop FK” messages, that simply means the index/FK already exists—skip that line and continue.

- File: `db/sql/db_patch_invoice_keys.sql`
  - Widens key text columns (e.g., `num_fact`/`num_offre`) to VARCHAR(32)
  - Ensures helpful indexes
  - Cleans stray whitespace
  - Re-creates FKs with robust names and proper ON DELETE/UPDATE rules

5) Fix “missing URL” issues (open only via main.php)
Problem: some links are `href="?Factures"` and assume pages are always loaded inside `main.php`. If someone browses directly to a PHP file under `pages/`, those relative links won’t work.

Solution implemented in this repo (no code changes needed):
- Root `.htaccess` (forces `main.php` as the directory index): `/.htaccess`
- `pages/.htaccess` (blocks direct HTTP access to partials, and redirects 403 to `main.php`): `/pages/.htaccess`

This keeps navigation stable and prevents loading partials directly.

6) Verify
- Visit: http://localhost/tpc/main.php
- Test key flows:
  - Factures add: http://localhost/tpc/main.php?Factures&Add
  - Offres: http://localhost/tpc/main.php?Offres_Prix

7) Common Issues
- 403 when opening a page file directly: expected (pages are partials). Access via `main.php`.
- DB connect errors: check `class/connexion.db.php` values and that the DB exists.
- Different Apache port (busy 80): change to 8080 in XAMPP and use `http://localhost:8080/tpc/main.php`.
- Timezone warnings: set `date.timezone` in `C:\xampp\php\php.ini`, restart Apache.

Appendix A – Reverting
- If something fails, stop Apache, restore your previous `tpc` backup folder, start Apache again.

