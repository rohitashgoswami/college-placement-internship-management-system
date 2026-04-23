# College Placement & Internship Management System

Final Year Project for BCA.

## Local setup

1. Create/import the database using [backend/database.sql](C:\xampp\htdocs\college-placement-internship-management-system\backend\database.sql).
2. Make sure MySQL is available with the same settings used in [backend/config/db.php](C:\xampp\htdocs\college-placement-internship-management-system\backend\config\db.php):
   host: `localhost`
   user: `root`
   password: ``
   database: `college_placement_system`
   port: `3307`

## Default admin login

- Username: `admin`
- Password: `admin123`

If someone cloned the repo and only created the tables manually, they also need the seeded admin row from [backend/database.sql](C:\xampp\htdocs\college-placement-internship-management-system\backend\database.sql), otherwise admin login will fail.
