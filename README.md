# instructorTask
## Features

### Authentication
- User signup and login with password hashing.
- JWT (JSON Web Token) is used to manage user sessions.
- Role-based access:
  - Admin: full access.
  - Editor: can add and edit only.

### Token System
- Access tokens expire after a short period (for example, 15 minutes).
- Refresh tokens last longer (for example, 7 days) and can be used to get a new access token without logging in again.

### Password Reset
- Users can request a password reset link using their email.
- A secure token is generated and stored temporarily in the database.
- The token expires after a set period (for example, 30 minutes).
- The user can then use this token to set a new password.

### Product Management Example
- CRUD operations for products (add, edit, delete, view).

Create a .env file in the project root:

APP_URL=http://localhost/itask
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=itask

JWT_SECRET=your_access_secret
JWT_REFRESH_SECRET=your_refresh_secret


API Routes
Authentication

| Method | Route                       | Description                           |
| ------ | --------------------------- | ------------------------------------- |
| POST   | /routes/signup.php          | Register new user                     |
| POST   | /routes/login.php           | Login and get access + refresh tokens |
| POST   | /routes/forgot_password.php | Request a password reset token        |
| POST   | /routes/reset_password.php  | Reset password with token             |
| POST   | /routes/refresh_token.php   | Renew access token                    |
| POST   | /routes/logout.php          | Invalidate refresh token              |

Product Routes

| Method | Route                     | Access        | Description       |
| ------ | ------------------------- | ------------- | ----------------- |
| GET    | /routes/products.php      | Admin, Editor | Get all products  |
| GET    | /routes/products.php?id=1 | Admin, Editor | Get product by ID |
| POST   | /routes/products.php      | Admin, Editor | Add a product     |
| PATCH  | /routes/products.php?id=1 | Admin, Editor | Update a product  |
| DELETE | /routes/products.php?id=1 | Admin         | Delete a product  |
| PATCH  | /routes/products.php?id=1 | Admin         | Restore a product |

