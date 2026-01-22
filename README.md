# Customer Management WordPress Plugin

## Overview
This plugin allows managing a custom database of customer records with full admin CRUD operations and a frontend display.

## Features
- Add/Edit/Delete/View customer records
- Fields: Name, Email, Phone, DOB, Age (calculated), Gender, CR Number, Address, City, Country, Status
- Automatic WordPress user creation if email is unique
- Admin dashboard with search and pagination
- Frontend display via shortcode `[customer_list]` with AJAX search & pagination

## Installation
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate from WordPress admin
3. On activation, the custom tables are created
4. Optional: Import SQL dump for schema + dummy data:  
   ```bash
   mysql -u username -p database_name < sql/customer_customers_dump.sql
