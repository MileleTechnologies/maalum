
Markdown
# Maalum Natural Swimming Pool - Waiver & Booking Form

A modern, responsive web application for managing waiver forms and bookings at the Maalum Natural Swimming Pool in Zanzibar. The system provides a multi-language interface, digital signature capture, and automated PDF generation with email notification.

## 🌍 Overview

Maalum is a comprehensive booking and waiver management system designed for natural swimming pools. It features:

- **Interactive HTML5 Form** with real-time validation
- **Digital Signature Capture** (draw, type, or upload)
- **Multi-Language Support** (8 languages: English, Spanish, French, German, Italian, Polish, Czech, Chinese)
- **PDF Generation & Email Delivery** of waiver documents
- **Responsive Design** for all devices (mobile, tablet, desktop)
- **CSRF Protection** for secure form submission
- **Comprehensive Data Validation** for all inputs

## 📋 Features

### Booking Management
- Booking reference name
- Supervisor/guardian information (with add multiple option)
- Booking date selection (future dates only)
- Contact information (email & phone)

### Visitor Information
- Adult participant details (name, age, email, phone)
- Children information (count and ages 0-17)
- Responsibility acknowledgment for children

### Signature Options
Users can sign in three ways:
1. **Draw** - Freehand signature on HTML5 canvas
2. **Type** - Text-based signature
3. **Upload** - Image file (JPG/PNG, max 3MB)

### Multi-Language Support
Seamless language switching via floating FAB button:
- 🇺🇸 English
- 🇪🇸 Español (Spanish)
- 🇫🇷 Français (French)
- 🇩🇪 Deutsch (German)
- 🇮🇹 Italiano (Italian)
- 🇵🇱 Polski (Polish)
- 🇨🇿 Čeština (Czech)
- 🇨🇳 中文 (Chinese)

### Automated Workflows
- Validates all form data before submission
- Generates PDF with terms & conditions
- Sends confirmation email with PDF attachment
- Displays success/error modals

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- Web server with PHP support (Apache/Nginx)
- SMTP credentials (Gmail or other provider)

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/MileleTechnologies/maalum.git
   cd maalum

2.Install Dependencies

bash
composer install

3.Configure Environment

bash
cp .env.example .env

4.Update .env with Your Settings

bash
SMTP_HOST=smtp.gmail.com
SMTP_PORT=465
SMTP_USER=your-email@gmail.com
SMTP_PASSWORD=your-app-password
RECIPIENT_EMAIL=info@maalumzanzibar.com

5.Deploy to Web Server

Upload files to your web hosting
Ensure proper file permissions
Access via https://yourdomain.com
📁 Project Structure

Code
maalum/
├── index.php                 # Main form interface
├── send.php                  # Form processing & email handler
├── assets/
│   └── styles.css           # Custom styling & responsive design
├── js/
│   └── materialize.min.js   # UI framework
├── PHPMailer/               # Email library
├── dompdf/                  # PDF generation library
├── MAALUM.png              # Logo image
├── composer.json           # Dependency management
├── .env.example            # Environment configuration template
├── .htaccess               # Apache rewrite rules
├── web.config              # IIS configuration
└── README.md               # This file

🔧 Configuration
Email Setup (Gmail Example)
Enable 2-Step Verification on Gmail
Generate an App Password at https://myaccount.google.com/apppasswords
Use the app password in .env:
Code
SMTP_PASSWORD=your-16-char-app-password

Customize Recipient Email
Edit send.php:

PHP
$mail->addAddress('your-email@domain.com'); // Change recipient


🎨 Customization
Terms & Conditions
Edit the 11 rules in index.php (lines with data-translate="rule{n}"):

PHP
<li data-translate="rule1">Your custom rule here</li>

Styling
Modify assets/styles.css for custom colors, fonts, and layout.

Logo
Replace MAALUM.png with your own logo (recommended: 200px width).

🔒 Security Features
CSRF Token Protection - Prevents cross-site request forgery
Input Validation - Server-side validation of all fields
Email Validation - Format checking for email addresses
File Upload Validation - Type and size restrictions for signatures
Date Validation - Prevents past booking dates
SQL Injection Prevention - Uses parameterized queries (when applicable)
XSS Protection - HTML entity encoding of user inputs


📊 Form Fields
Field	Type	Required	Validation
Booking Name	Text	Yes	Non-empty
Supervisor Name(s)	Text	Yes	At least 1 required
Email	Email	Conditional	Valid format OR phone required
Phone	Tel	Conditional	Valid format OR email required
Booking Date	Date	Yes	Today or future
Children	Checkbox	No	-
Children Count	Select	Conditional	1-10 if children selected
Child Ages	Number	Conditional	0-17 each
Adults	Array	No	Name, age (12-120), email, phone
Signature	Mixed	Yes	Draw/Type/Upload option
Terms Agreement	Checkbox	Yes	Must check


📧 Email Output
The system sends an email containing:

All submitted booking information
Children details and adult information
Submission timestamp
Attached PDF waiver document

📱 Responsive Breakpoints
Mobile (≤480px) - Single column, stacked layout
Tablet (481px-768px) - Two-column where possible
Desktop (≥769px) - Full responsive grid

🐛 Troubleshooting
Email Not Sending
Check SMTP credentials in .env
Verify firewall isn't blocking SMTP port (465)
Check Gmail App Passwords if using Gmail
Enable "Less secure apps" (if not using App Passwords)
PDF Not Generating
Ensure dompdf folder exists and is readable
Check PHP temp directory permissions
Verify DOMPDF dependencies are installed
Form Not Validating
Check browser console for JavaScript errors
Verify Materialize JS library is loaded
Check that form field IDs match JavaScript references
Images Not Displaying
Ensure MAALUM.png is in the root directory
Check file permissions (should be 644)
Verify correct file path in HTML

🌐 Browser Support
Chrome 90+
Firefox 88+
Safari 14+
Edge 90+
Mobile browsers (iOS Safari, Chrome Mobile)

👥 Support
For issues or questions, contact:

Email: support@mileletechnologies.com
email: flavianmichael663@gmail.com
Website: https://www.mileletechnologies.com

🔄 Version History
v1.0.0 (Current)
Initial release
8-language support
Digital signature capture (3 modes)
PDF generation & email delivery
CSRF protection
Mobile-responsive design

📚 Dependencies
Materialize CSS - Frontend framework
PHPMailer - Email sending
DOMPDF - PDF generation
Signature Pad - Canvas signature

🚢 Deployment
Apache (.htaccess included)
Code
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Your rewrite rules
</IfModule>


Developed by: Milele Technologies
Last Updated: February 26, 2026
Repository: https://github.com/MileleTechnologies/maalum
