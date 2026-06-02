============================================
  ANTIQUE FURNITURE WORKSHOP
  Localhost Deployment Instructions
============================================

REQUIREMENTS:
- XAMPP (https://www.apachefriends.org/)
- A modern web browser (Chrome, Firefox, Edge, Safari)

SETUP STEPS:

1. INSTALL XAMPP
   - Download XAMPP from https://www.apachefriends.org/
   - Run the installer and follow the setup wizard
   - Default installation is fine for this project

2. PLACE THE PROJECT FOLDER
   - Copy the entire "antique-furniture-workshop" folder
   - Paste it inside C:\xampp\htdocs\
   
   Final path should be:
   C:\xampp\htdocs\antique-furniture-workshop\

3. START APACHE & MYSQL
   - Open XAMPP Control Panel
   - Click "Start" next to Apache
   - Click "Start" next to MySQL
   - Wait for both to show green status

4. SET UP THE DATABASE
   - Open your browser
   - Navigate to: http://localhost/antique-furniture-workshop/install.php
   - Leave the default settings (host: localhost, user: root, password: empty)
   - Click "Install Database"
   - After success, delete install.php for security

5. ACCESS THE WEBSITE
   - Open your browser
   - Navigate to: http://localhost/antique-furniture-workshop/
   - You should see the homepage

PAGES:
- Home:     http://localhost/antique-furniture-workshop/index.php
- About:    http://localhost/antique-furniture-workshop/about.php
- Gallery:  http://localhost/antique-furniture-workshop/gallery.php
- Services: http://localhost/antique-furniture-workshop/services.php
- Contact:  http://localhost/antique-furniture-workshop/contact.php
- FAQ:      http://localhost/antique-furniture-workshop/faq.php

ADMIN PANEL:
- URL:      http://localhost/antique-furniture-workshop/admin/
- Login with the credentials you set during installation

  You can change credentials in Admin Panel > Settings.

CONTACT FORM:
- The contact form requires Apache + PHP + MySQL (XAMPP) to process
- Submitted messages are stored in the database
- View messages via the Admin Panel > Messages
- Contact form includes Google reCAPTCHA v2 for spam protection
- Message field limited to 250 words

FEATURES:
- Responsive design (mobile, tablet, desktop)
- Gallery with category filters and search
- Interactive map (OpenStreetMap)
- Chatbot for quick FAQ answers
- Accessibility: skip-to-content, font controls, high-contrast mode
- Scroll up/down buttons
- Client testimonials
- Animated stats counter, parallax effects, micro-animations
- Admin dashboard with reports, inventory, categories, sales management

NOTES:
- MySQL database setup IS required (see step 4)
- No npm or composer dependencies
- The site works after placing in htdocs and running install.php
- All assets use relative paths
- No user login/signup — this is a portfolio/showcase website

============================================
  (c) 2024 Antique Furniture Workshop
============================================
