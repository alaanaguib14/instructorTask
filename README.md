# instructorTask
Features
Authentication

Users can sign up and log in with password hashing.

JWT is used for secure session management.

Role-based access control:

Admin: full access.

Editor: can add and edit only.

Token System

Access Token: short-lived (e.g., 15–30 minutes).

Refresh Token: long-lived (e.g., 14 days), stored securely in the database.

Users can request a new access token using the refresh token without logging in again.

Logout invalidates the refresh token in the database.

Password Reset

Users can request a password reset link by email.

A secure token is generated and stored in the database temporarily.

The token expires after a set time (e.g., 30 minutes).

Users can then use the token to set a new password.

Product Management

Full CRUD operations (create, read, update, delete, restore).

Products include name, description, price, category, and image.

Category Management

CRUD operations for categories.

Each category has a name and description.

Environment Setup

Create a .env file in your project root and add the following variables:

DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=itask

JWT_SECRET=your_access_secret
JWT_REFRESH_SECRET=your_refresh_secret


API Routes
Authentication Routes

| Method   | Route                         | Description                                          |
| -------- | ----------------------------- | ---------------------------------------------------- |
| **POST** | `/routes/signup.php`          | Register a new user                                  |
| **POST** | `/routes/login.php`           | Log in and receive access + refresh tokens           |
| **POST** | `/routes/forgot_password.php` | Request a password reset token                       |
| **POST** | `/routes/reset_password.php`  | Reset password using a valid token                   |
| **POST** | `/routes/refresh_token.php`   | Refresh the access token using a valid refresh token |
| **POST** | `/routes/logout.php`          | Log out and invalidate the refresh token             |


Product Routes

| Method     | Route                                    | Access        | Description               |
| ---------- | ---------------------------------------- | ------------- | ------------------------- |
| **GET**    | `/routes/products.php`                   | Admin, Editor | Get all products          |
| **GET**    | `/routes/products.php?id=1`              | Admin, Editor | Get a product by ID       |
| **POST**   | `/routes/products.php`                   | Admin, Editor | Add a new product         |
| **PATCH**  | `/routes/products.php?id=1`              | Admin, Editor | Update a product          |
| **DELETE** | `/routes/products.php?id=1`              | Admin         | Soft delete a product     |
| **PATCH**  | `/routes/products.php?id=1&restore=true` | Admin         | Restore a deleted product |

Category Routes
| Method     | Route                                      | Access        | Description                |
| ---------- | ------------------------------------------ | ------------- | -------------------------- |
| **GET**    | `/routes/categories.php`                   | Admin, Editor | Get all categories         |
| **GET**    | `/routes/categories.php?id=1`              | Admin, Editor | Get a category by ID       |
| **POST**   | `/routes/categories.php`                   | Admin, Editor | Add a new category         |
| **PATCH**  | `/routes/categories.php?id=1`              | Admin, Editor | Update a category          |
| **DELETE** | `/routes/categories.php?id=1`              | Admin         | Soft delete a category     |
| **PATCH**  | `/routes/categories.php?id=1&restore=true` | Admin         | Restore a deleted category |
