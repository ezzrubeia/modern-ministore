# Modern Mini Store 

## Project Description

Modern MiniStore is a PHP and MySQL mini market web application. The project runs locally using XAMPP and allows users to browse products, add products to the cart, and complete a Cash-on-Delivery order. The project also includes admin pages for managing products and viewing orders.

---

## Tools and Technologies Used

- PHP
- MySQL
- PDO
- HTML
- CSS
- JavaScript
- XAMPP
- Apache
- phpMyAdmin

---

## How to Run the Project

1. Install XAMPP.

2. Copy the project folder:

   mini_market_project

   into:

   C:/xampp/htdocs/

3. Open XAMPP Control Panel.

4. Start:

   Apache
   MySQL

5. Open phpMyAdmin in the browser:

   http://localhost/phpmyadmin/

6. Create a new database named:

   mini_market_project

7. Import the SQL files in this order:
    1. schema_cod.sql
    2. schema_users.sql

8. Open the project configuration file:

   config.php

   and make sure the database settings are correct:

   DB_HOST = localhost
   DB_NAME = mini_market_project
   DB_USER = root
   DB_PASS = empty password

9. Open the project in the browser:

   http://localhost/mini_market_project/

---

## How to Use the Project

### User

1. Open the website from localhost.
2. Register a new account.
3. Login using email and password.
4. Browse the products.
5. Open a product details page.
6. Add products to the cart.
7. Open the cart page and review the order.
8. Go to checkout.
9. Enter the required customer information.
10. Submit the Cash-on-Delivery order.
11. View the order confirmation page.

### Admin

1. Login using the admin account.
2. Open the admin dashboard.
3. Manage products by adding, editing, or deleting products.
4. View customer orders from the admin orders page.

---

## Admin Login

Default admin account:

 Email: admin@store.com
 Password: Admin1234

If this account does not work, check the admin user record in the database.

---

## Project Files

- config.php: database settings and project configuration.
- db.php: database connection using PDO.
- auth.php: login, session, and admin access functions.
- index.php: homepage and product listing.
- product_details.php: product details page.
- cart.php: shopping cart page.
- cart_add.php: add product to cart.
- checkout.php: checkout page.
- thankyou.php: order confirmation page.
- user_register.php: user registration page.
- user_login.php: user login page.
- user_logout.php: logout page.
- admin_dashboard.php: admin dashboard.
- admin_products.php: admin product management.
- admin_orders.php: admin orders page.
- api/products.php: product API.
- api/cod_place_order.php: Cash-on-Delivery order processing.
- assets/style.css: project styling.
- assets/app.js: frontend JavaScript.
- uploads/: product images folder.
- schema_cod.sql: main database tables.
- schema_users.sql: users and admin database setup.

---

## Setup Notes

- The project must be placed inside the XAMPP htdocs folder.
- Apache and MySQL must be running before opening the project.
- The database name must match the name used in config.php.
- If the project folder name is changed, update BASE_URL inside config.php.
- Make sure the uploads folder exists so product images can be displayed correctly.
