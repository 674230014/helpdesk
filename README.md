# Helpdesk

This repository contains a PHP-based helpdesk and ticket management application for handling support requests, technician assignment, progress tracking, and customer communication.

The application lives in the `helpdesk/` directory. For detailed project documentation, see `helpdesk/README.md`.

## Features

- Ticket creation and assignment workflow
- Role-based access control for admins, technicians, and users
- Status transitions such as Open, Assigned, In Progress, Resolved, and Closed
- File uploads and comments for incident tracking
- Admin dashboard with ticket statistics
- SQLite or MySQL support
- LINE notification support with logging fallback

## Tech Stack

- PHP 8.2+
- Custom MVC architecture
- Composer autoloading
- PDO for database access
- Tailwind CSS for frontend styling
- SQLite by default, MySQL supported

## Project Structure

```text
helpdesk/
├── bin/
├── database/
├── public/
├── src/
├── storage/
├── views/
├── .env.example
├── composer.json
├── README.md
└── ...
```

## Quick Start

1. Change into the app directory:

```bash
cd helpdesk
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy the environment file:

```bash
cp .env.example .env
```

4. Initialize the database and seed demo data:

```bash
php bin/install.php
```

5. Run the app locally:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Then open http://127.0.0.1:8000 in your browser.

## Demo Accounts

The default seeded accounts are:

- Admin: `admin@example.com` / `admin1234`
- Technician: `tech1@example.com` / `tech1234`
- Technician: `tech2@example.com` / `tech1234`
- User: `user1@example.com` / `user1234`

> Change the default passwords before using the app in a real environment.

## Development Notes

- SQLite works out of the box.
- For MySQL, update the database settings in `.env` and run `php bin/install.php` again.
- Uploaded files are stored under the app's writable storage paths.
- When LINE credentials are not configured, the app logs notification attempts instead of sending messages.

## License

This project is distributed without a formal license file in the repository root. Please check the contents of the `helpdesk/` project for any project-specific licensing or usage notes before deploying it in production.

## Related Documentation

For deeper technical details, architecture flow, deployment guidance, and ticket lifecycle information, see:

- `helpdesk/README.md`
