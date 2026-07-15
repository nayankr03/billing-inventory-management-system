# 🚀 Billing Inventory Management System (BIMS)

![PHP](https://img.shields.io/badge/PHP-8.x-blue?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-purple?style=for-the-badge&logo=bootstrap)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6-yellow?style=for-the-badge&logo=javascript)
![Version](https://img.shields.io/badge/Version-v1.0-success?style=for-the-badge)

> **A complete Billing & Inventory Management System developed using PHP, MySQL, Bootstrap, JavaScript, and AJAX.**

---

# 📌 Overview

Billing Inventory Management System (BIMS) is a web-based application designed to simplify inventory management, customer handling, billing, sales tracking, and business reporting.

It provides an easy-to-use interface for managing products, generating invoices, tracking sales, monitoring inventory, and viewing business reports.

---

# ✨ Features

## 🔐 Authentication

- Secure Login
- Logout
- Session Management
- Protected Pages

---

## 📦 Product Management

- Add Product
- Edit Product
- Delete Product
- Product Search
- Product Image Upload
- Product Code
- Stock Management
- Low Stock Indicator

---

## 👥 Customer Management

- Add Customer
- Edit Customer
- Delete Customer
- Live Customer Search (AJAX)
- Search by Mobile Number
- Quick Add Customer
- Customer Details Card

---

## 🧾 Billing Module

### Customer

- Live Search
- Auto Select Customer

### Products

- Live Product Search
- Search by Product Name
- Search by Product Code

### Invoice Cart

- Add Product
- Remove Product
- Increase Quantity
- Decrease Quantity
- Merge Duplicate Products
- Automatic Grand Total
- Stock Validation

### Sales

- Save Invoice
- Database Transactions
- Automatic Stock Deduction

---

## 📄 Professional Invoice

- Company Logo
- Company Details
- Customer Information
- Product Table
- Grand Total
- Payment Method
- Print Invoice
- Responsive Print Layout

---

## 📜 Sales Management

- Sales History
- Search Invoice
- Search Customer
- Search Mobile Number
- Date Filter
- Pagination
- View Invoice
- Print Invoice
- Delete Sale
- Automatic Stock Restore

---

## 📊 Reports Dashboard

- Today's Sales
- Monthly Revenue
- Total Invoices
- Products Sold
- Top Selling Products
- Top Customers
- Low Stock Report
- Recent Sales

---

## ⚙ Company Settings

- Company Name
- Owner Name
- Phone
- Email
- GST Number
- Address
- Company Logo Upload
- Dynamic Invoice Branding

---

# 🛠 Technology Stack

- PHP
- MySQL
- HTML5
- CSS3
- Bootstrap 5
- JavaScript (ES6)
- Fetch API (AJAX)
- SweetAlert2

---

# 🗄 Database Tables

- users
- products
- customers
- sales
- sale_items
- company_settings

---

# 📂 Project Structure

```
BIMS/
│
├── assets/
├── includes/
├── pages/
├── uploads/
│   ├── logo/
│   └── products/
├── database/
├── login.php
├── dashboard.php
└── README.md
```

---

# 🔄 System Workflow

```
Login
   │
   ▼
Dashboard
   │
   ▼
Products
   │
   ▼
Customers
   │
   ▼
Create New Sale
   │
   ▼
Invoice Cart
   │
   ▼
Save Sale
   │
   ▼
Update Stock
   │
   ▼
Generate Invoice
   │
   ▼
Sales History
   │
   ▼
Reports Dashboard
```

---

# 📸 Screenshots

> Add screenshots of the following pages:

- Login Page
- Dashboard
- Product Management
- Customer Management
- New Sale
- Invoice
- Sales History
- Reports Dashboard
- Company Settings

---

# 🚀 Installation

1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/BIMS.git
```

2. Move the project to your XAMPP `htdocs` folder.

3. Import the MySQL database.

4. Configure database credentials in:

```
includes/config.php
```

5. Start Apache & MySQL.

6. Open:

```
http://localhost/BIMS
```

---

# 📌 Future Enhancements (v1.1)

- 👤 User Management
- 🔐 Role & Permission System
- 📄 PDF Invoice Download
- 📦 Purchase Management
- 💸 Expense Management
- 🧾 GST Calculation
- 📷 Barcode Scanner
- 💾 Backup & Restore
- 📱 WhatsApp Invoice Sharing

---

# 👨‍💻 Developed By

**Nayan Kumar**
---

# 📜 License

This project is developed for educational and portfolio purposes.

---

# ⭐ If you found this project helpful

Please consider giving it a ⭐ on GitHub!
