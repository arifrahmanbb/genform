# GenForm

A powerful drag-and-drop form builder plugin for WordPress.

## Description

GenForm is a modern, intuitive form builder plugin that allows you to create beautiful, responsive forms with ease. Built with performance and security in mind, GenForm follows WordPress coding standards and best practices.

## Features

### Core Fields
- Single Line Text
- Paragraph Text (Textarea)
- Email
- Number
- Radio Buttons
- Checkboxes
- Dropdown
- Submit Button

### Form Builder
- Drag & drop interface
- Live preview
- Field customization (label, placeholder, required, CSS class)
- Form settings panel

### Submission Management
- Secure database storage
- Entry viewer with sorting
- Individual entry details
- IP tracking and user agent logging

### Email Notifications
- Admin notifications
- User confirmation emails
- Customizable email templates

### Spam Protection
- Honeypot implementation
- Nonce verification

### Frontend Integration
- Shortcode support: `[genform id="X"]`
- Gutenberg block
- Responsive design
- AJAX form submission

## Installation

1. Upload the `genform` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to GenForm in the admin menu to create your first form

## Usage

### Creating a Form

1. Go to **GenForm > Add New**
2. Enter a form name
3. Add fields by clicking on field types in the sidebar
4. Configure field settings by clicking "Edit" on each field
5. Configure form settings (success message, redirect URL, email notifications)
6. Click "Save Form"

### Displaying a Form

**Using Shortcode:**
```
[genform id="1"]
```

**Using Gutenberg Block:**
1. Add a new block
2. Search for "GenForm"
3. Select your form from the dropdown

## Frequently Asked Questions

### How do I view form submissions?

Go to **GenForm > Entries** in the WordPress admin menu.

### Can I customize email notifications?

Yes! Each form has settings for admin and user confirmation emails with customizable subjects and messages.

### Is the plugin GDPR compliant?

The plugin stores submission data in your WordPress database. You are responsible for adding appropriate privacy notices and obtaining user consent as required by GDPR.

## Changelog

### 1.0.0
- Initial release
- Drag & drop form builder
- 8 core field types
- Email notifications
- Honeypot spam protection
- Gutenberg block integration
- Shortcode support

## License

This plugin is licensed under GPL v3 or later.

## Support

For support, please visit the WordPress.org support forums.