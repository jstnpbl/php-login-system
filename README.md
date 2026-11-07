# PHP Login System with Admin Approval

A PHP and MySQL login system where new registrations stay **pending** until an administrator approves them. Only approved users can log in.

> **Learning project.** It follows common security practice for a small PHP app (see [Security design](#security-design)), but it has not had a professional security review, and it is meant for local use and learning. Read [Limitations](#limitations) before using it for anything real.

## Features

- **Registration** with validation (username rules, password length, confirmation)
- **Admin approval:** an administrator approves or rejects each registration
- **Approval-gated login:** only users with the `approved` status can log in
- **Admin panel:** protected by a real administrator role, with no way to read anyone's password
- **Sessions and dashboard:** logged-in users reach a simple dashboard and can log out
- **Account lockout** after repeated wrong passwords

## Technologies

- PHP 8 (`mysqli`, sessions, `password_hash`)
- MySQL / MariaDB
- HTML (no JavaScript needed)

## How it works

1. A visitor registers on `register.php`. Their password is hashed and the account is saved as `pending`.
2. An administrator logs in, opens the admin panel, and approves or rejects the request.
3. Once approved, the user can log in. Pending and rejected users are told why they can't.
4. Access is checked against the database on **every** request, so rejecting a user logs them out immediately.

## Project structure

| File | Purpose |
|---|---|
| `index.php` | Login page |
| `register.php` | Registration form and handler |
| `login.php` | Checks the password, starts the session |
| `dashboard.php` | Page for logged-in users |
| `logout.php` | Ends the session (POST only) |
| `admin.php` | Admin panel: lists users, with Approve / Reject buttons |
| `approve.php`, `reject.php` | Change a user's status (admins only) |
| `create_admin.php` | Creates the first admin account (command line only) |
| `helpers.php` | Shared security helpers (escaping, CSRF tokens, access checks, validation) |
| `db.php` | Opens the database connection |
| `config.example.php` | Template for your database settings |
| `database.sql` | Database structure (no data) |

## How to run locally

You need [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP 8 + MySQL setup).

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Clone the repository into XAMPP's `htdocs` folder:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/jstnpbl/php-login-system.git
   cd php-login-system
   ```
3. Create your settings file by copying the template. The defaults match a fresh XAMPP install:
   ```
   copy config.example.php config.php
   ```
4. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), go to the **SQL** tab, paste the contents of `database.sql`, and run it. This creates the `php_login_system` database.
5. Create the first administrator from the command line:
   ```
   C:\xampp\php\php.exe create_admin.php
   ```
   Enter a username and a password (8 to 72 characters). The password is visible while you type, so close the window afterwards.
6. Open `http://localhost/php-login-system/` and log in. The admin goes straight to the admin panel.

To try the full flow: register a normal account, log in as the admin, approve it, then log in as that user.

## Security design

What this version does, and why:

| Protection | How |
|---|---|
| Passwords are never stored | Only a salted one-way hash from `password_hash()`. Checked with `password_verify()`. No one, including an admin, can read a password. |
| SQL injection | Every query that uses outside input is a prepared statement. IDs are checked to be whole numbers first. |
| Admin pages are locked | `admin.php`, `approve.php`, and `reject.php` require a logged-in user whose role in the database is `admin`. |
| Cross-site request forgery (CSRF) | Every form carries a secret token that is verified on submit. Logout is POST-only. |
| Cross-site scripting (XSS) | Everything printed from the database or a user goes through `e()` (`htmlspecialchars`). Usernames are limited to letters, numbers, and underscores. |
| Session fixation | A new session ID is issued at login. Session cookies are `HttpOnly` and `SameSite=Lax`. |
| Immediate revocation | Status and role are re-read from the database on every protected request. |
| Password guessing | 5 wrong passwords lock the account for 5 minutes. A wrong username and a wrong password give the same message and take about the same time. |
| Unique usernames | A `UNIQUE` database rule, case-insensitive. |
| Safe errors | Database errors are written to the server log, not shown to visitors. |
| Settings out of the code | Database settings live in `config.php`, which is listed in `.gitignore`. There are no passwords or keys in the repository. |
| Browser safety headers | `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, and `Cache-Control: no-store` on private pages. |

## Limitations

- **No HTTPS.** Passwords travel in plain text over plain HTTP, which is acceptable only on `localhost`. Before putting it on a real server, use HTTPS and turn on the `secure` cookie option in `helpers.php`.
- **No password reset, email verification, or "remember me".**
- **No session timeout.** A session lasts until logout or until the browser closes.
- **The lockout works per account.** It slows down guessing, but someone could also deliberately lock another person's account by guessing wrongly on purpose.
- **Rejected usernames stay reserved**, because rejecting keeps the record.
- **The first admin is created from the command line.** There is no admin-management screen.
- **It was tested with automated HTTP checks** on PHP 8.3 and MariaDB 10.11 (Linux, using PHP's built-in web server), not on a Windows XAMPP install.

## History

The first version of this project stored usernames and passwords with reversible AES encryption using a key in the source code, let an admin "decrypt" and view user passwords, and had no login protection on the admin page. This version replaces that design. The old approach is a good example of why passwords should be hashed, not encrypted.

## License

Add a license of your choice (for example MIT) if you want others to reuse this code.
