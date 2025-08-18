# MailFlow - PHP Email Management System

A comprehensive email management system built with PHP, featuring a modern admin interface and full email functionality.

## Features

- **User Authentication & Authorization**
  - Secure login/logout system
  - Role-based access control (Administrator, Manager, User)
  - Failed login attempt protection
  - Session management

- **Email Management**
  - Send, receive, and organize emails
  - Draft saving functionality
  - Email attachments support
  - Star/unstar emails
  - Search functionality
  - Folder organization (Inbox, Sent, Drafts, Spam, Trash)

- **Admin Dashboard**
  - System status monitoring
  - User management
  - Audit logging
  - Storage usage tracking
  - Mail queue monitoring

- **Modern UI/UX**
  - Responsive design with Tailwind CSS
  - Material Design icons
  - Smooth animations and transitions
  - Mobile-friendly interface

## Installation

1. **Database Setup**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Configuration**
   - Update database credentials in `includes/config.php`
   - Configure SMTP settings for email sending
   - Set appropriate file permissions

3. **Web Server**
   - Ensure PHP 7.4+ is installed
   - Configure your web server to serve the application
   - Enable required PHP extensions (PDO, mysqli)

## Default Login

- **Email:** admin@mailflow.com
- **Password:** admin123

## File Structure

```
mailflow/
├── includes/
│   ├── config.php          # Database and app configuration
│   ├── auth.php            # Authentication functions
│   └── functions.php       # Core application functions
├── ajax/
│   ├── toggle_star.php     # AJAX endpoint for starring emails
│   └── delete_email.php    # AJAX endpoint for deleting emails
├── index.php               # Main dashboard
├── login.php               # Login page
├── logout.php              # Logout handler
├── compose.php             # Email composition
├── email.php               # Email viewing
├── database.sql            # Database schema
└── README.md               # This file
```

## Database Schema

The application uses the following main tables:

- `users` - User accounts and authentication
- `emails` - Email messages and metadata
- `email_attachments` - File attachments
- `email_labels` - Custom labels/tags
- `email_queue` - Email sending queue
- `audit_logs` - System activity logging
- `notifications` - User notifications
- `system_settings` - Application configuration

## Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection for forms
- Session security with timeout
- Failed login attempt limiting
- Audit logging for security events

## API Endpoints

The application includes AJAX endpoints for dynamic functionality:

- `POST /ajax/toggle_star.php` - Toggle email star status
- `POST /ajax/delete_email.php` - Delete/move email to trash
- `POST /ajax/auto_save.php` - Auto-save draft emails

## Customization

The application uses a modular structure that makes it easy to:

- Add new email folders/categories
- Implement custom email filters
- Extend the admin dashboard
- Add new user roles and permissions
- Integrate with external email services

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser with JavaScript enabled

## License

This project is open source and available under the MIT License.