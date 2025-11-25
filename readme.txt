=== GenForm - Drag & Drop Form Builder ===
Contributors: arifrahman1
Tags: form builder, contact form, drag and drop, forms, email
Requires at least: 6.0
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create beautiful, responsive forms with an intuitive drag-and-drop builder. Includes email notifications, spam protection, and entry management.

== Description ==

**GenForm** is a powerful yet simple form builder that makes creating WordPress forms effortless. Whether you need a contact form, quote request, or support ticket form, GenForm has you covered.

= 🎯 Why Choose GenForm? =

* **Easy to Use** - Intuitive drag-and-drop interface, no coding required
* **Fast & Lightweight** - Optimized for performance
* **Secure** - Built-in spam protection and follows WordPress security standards
* **Responsive** - Forms look perfect on all devices
* **Developer Friendly** - Clean code following WordPress standards

= ✨ Key Features =

**Form Builder**
* Drag & drop interface with live preview
* 8 essential field types
* Customizable field labels, placeholders, and CSS classes
* Mark fields as required or optional
* Reorder fields easily

**Field Types**
* Single Line Text
* Paragraph Text (Textarea)
* Email
* Number
* Radio Buttons
* Checkboxes
* Dropdown Select
* Submit Button

**Submission Management**
* View all entries in admin dashboard
* Filter entries by form
* Track submission date, IP address, and user agent
* Detailed entry view

**Email Notifications**
* Automatic admin notifications on new submissions
* Optional user confirmation emails
* Customizable email subjects and messages

**Spam Protection**
* Honeypot technique (invisible to users)
* WordPress nonce verification
* No annoying CAPTCHAs needed

**Display Options**
* Simple shortcode: `[genform id="1"]`
* AJAX submission (no page reload)
* Custom success messages
* Redirect to thank you page option

= 🚀 Perfect For =

* Contact forms
* Quote request forms
* Support tickets
* Feedback forms
* And much more!

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Go to Plugins > Add New
3. Search for "GenForm"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the downloaded file and click "Install Now"
4. Activate the plugin

= Getting Started =

1. Go to **GenForm > Add New** in your WordPress admin
2. Give your form a name
3. Drag field types from the sidebar to build your form
4. Click "Edit" on each field to customize settings
5. Configure form settings (emails, success message, redirect URL)
6. Click "Save Form"
7. Copy the shortcode to display your form

== Frequently Asked Questions ==

= Is GenForm free? =

Yes! GenForm is completely free and open source.

= How do I add a form to my page? =

After creating a form:
1. Copy the shortcode `[genform id="X"]` (replace X with your form ID)
2. Paste it into any page, post, or widget
3. Find the shortcode in GenForm > All Forms

= Where are form submissions stored? =

All submissions are securely stored in your WordPress database. Go to **GenForm > Entries** to view them.

= Can I customize the email notifications? =

Yes! Each form has individual settings for:
* Admin notification email address
* Email subject line
* Email message content
* User confirmation emails (optional)

= How does spam protection work? =

GenForm uses a honeypot field (invisible to real users) and WordPress nonce verification to block spam bots without annoying your visitors with CAPTCHAs.

= Can I export form entries? =

Currently, entries are displayed in the admin panel. You can view and copy the data from the detailed entry view.

= Is it GDPR compliant? =

GenForm stores data in your WordPress database. You're responsible for:
* Adding privacy notices to your forms
* Obtaining user consent where required
* Managing data according to GDPR requirements

= Does it work with my theme? =

Yes! GenForm is designed to work with any properly coded WordPress theme.

= Can I customize the form styling? =

Yes! You can:
* Add custom CSS classes to fields
* Use your theme's styles (forms inherit theme styling)
* Add custom CSS in your theme or child theme

= Can I redirect users after form submission? =

Yes! In the form settings, you can either show a success message or redirect users to a custom thank you page URL.

== Screenshots ==

1. Drag & drop form builder interface
2. Form settings panel with email configuration
3. Entries management dashboard
4. Field configuration options

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
First release of GenForm - start building beautiful forms today!
