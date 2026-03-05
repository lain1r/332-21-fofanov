# PHP Authentication & Portfolio Project

This is a personal portfolio project that includes a secure user authentication system built with PHP and SQLite.

## Features
- **Registration**: Register new users with phone number, password, and a secret question for password recovery.
- **Login**: Secure login with hashed passwords.
- **Password Recovery**: Recover lost passwords using the secret question.
- **Protected Portfolio**: A personal portfolio page accessible only to authenticated users.

## Requirements
- PHP 7.4 or higher
- Node.js (for running the startup script)
- SQLite3 extension enabled in PHP (`extension=sqlite3` and `extension=pdo_sqlite` in `php.ini`)

## Installation and Execution
1. Clone the repository.
2. Open terminal in the project directory.
3. Run the following command to start the built-in PHP server:
   ```bash
   npm start
   ```
   Alternatively, you can start the server manually using:
   ```bash
   php -S localhost:8000
   ```
4. Access the application in your browser at `http://localhost:8000`.

## Database Structure
The application uses an SQLite database `database.db` located in the `backend/` directory. It is created automatically upon the first request.
- `users` table:
  - `id` (INTEGER, PRIMARY KEY)
  - `phone` (TEXT, UNIQUE) - Format: +7-XXX-XXX-XX-XX
  - `password` (TEXT) - Hashed with `password_hash()`
  - `secret_question` (TEXT)
  - `secret_answer` (TEXT) - Hashed with `password_hash()`
  - `created_at` (DATETIME)

## Security Measures
- Passwords and secret answers are securely hashed using PHP's native `password_hash()`.
- SQL queries use Prepared Statements via PDO to prevent SQL Injection.
- Cross-Site Request Forgery (CSRF) tokens are implemented on all forms.
- Session hijacking prevention measures.
