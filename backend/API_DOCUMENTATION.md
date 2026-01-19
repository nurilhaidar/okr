# OKR System API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
All endpoints (except `/login`) require a Bearer token in the Authorization header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## Authentication Endpoints

### Login
Authenticate user and receive token.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | Login with email and password |

**Request Body:**
```json
{
  "email": "admin@okr.com",
  "password": "password"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@okr.com",
      "username": "admin",
      "role": "Admin"
    }
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Not logged in yet"
}
```

---

### Logout
Logout current user and invalidate token.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/logout` | Logout authenticated user |

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Get Current User
Get currently authenticated user information.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/me` | Get current user data |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@okr.com",
      "username": "admin",
      "role": "Admin",
      "position": "Tech Lead",
      "rank": "C-Level"
    }
  }
}
```

---

## Employee Endpoints

### List All Employees
Get all employees with their relationships.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/employees` | Get all employees |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@okr.com",
      "username": "admin",
      "is_active": true,
      "rank": { "id": 7, "name": "C-Level" },
      "position": { "id": 3, "name": "Tech Lead" },
      "role": { "id": 1, "name": "Admin" }
    }
  ]
}
```

---

### Create Employee
Create a new employee.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/employees` | Create new employee |

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "username": "johndoe",
  "password": "password123",
  "rank_id": 1,
  "position_id": 1,
  "role_id": 1,
  "is_active": true
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Employee created successfully",
  "data": {
    "id": 6,
    "name": "John Doe",
    "email": "john@example.com",
    "username": "johndoe",
    "is_active": true,
    "rank": { "id": 1, "name": "Junior" },
    "position": { "id": 1, "name": "Software Engineer" },
    "role": { "id": 1, "name": "Admin" }
  }
}
```

---

### Get Single Employee
Get details of a specific employee.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/employees/{id}` | Get employee by ID |

**Parameters:**
- `id` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@okr.com",
    "username": "admin",
    "is_active": true,
    "rank": { "id": 7, "name": "C-Level" },
    "position": { "id": 3, "name": "Tech Lead" },
    "role": { "id": 1, "name": "Admin" },
    "org_units": [...]
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Employee not found"
}
```

---

### Update Employee
Update an existing employee.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/employees/{id}` | Update employee by ID |

**Parameters:**
- `id` (path) - Employee ID

**Request Body:**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "username": "johnupdated",
  "password": "newpassword123",
  "rank_id": 2,
  "position_id": 2,
  "role_id": 2,
  "is_active": false
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Employee updated successfully",
  "data": {
    "id": 6,
    "name": "John Updated",
    "email": "john.updated@example.com",
    "username": "johnupdated",
    "is_active": false,
    ...
  }
}
```

---

### Deactivate Employee
Deactivate an employee account.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/employees/{id}/deactivate` | Deactivate employee |

**Parameters:**
- `id` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "message": "Employee deactivated successfully",
  "data": {
    "id": 6,
    "is_active": false,
    ...
  }
}
```

---

### Activate Employee
Activate a deactivated employee account.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/employees/{id}/activate` | Activate employee |

**Parameters:**
- `id` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "message": "Employee activated successfully",
  "data": {
    "id": 6,
    "is_active": true,
    ...
  }
}
```

---

### Delete Employee
Permanently delete an employee.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/employees/{id}` | Delete employee by ID |

**Parameters:**
- `id` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "message": "Employee deleted successfully"
}
```

---

## Role Endpoints

### List All Roles
Get all roles with their employees.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/roles` | Get all roles |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Admin",
      "employees": [...]
    }
  ]
}
```

---

### Create Role
Create a new role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/roles` | Create new role |

**Request Body:**
```json
{
  "name": "Manager"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Role created successfully",
  "data": {
    "id": 6,
    "name": "Manager"
  }
}
```

---

### Get Single Role
Get details of a specific role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/roles/{id}` | Get role by ID |

**Parameters:**
- `id` (path) - Role ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin",
    "employees": [...]
  }
}
```

---

### Update Role
Update an existing role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/roles/{id}` | Update role by ID |

**Parameters:**
- `id` (path) - Role ID

**Request Body:**
```json
{
  "name": "Senior Manager"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Role updated successfully",
  "data": {
    "id": 6,
    "name": "Senior Manager"
  }
}
```

---

### Delete Role
Permanently delete a role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/roles/{id}` | Delete role by ID |

**Parameters:**
- `id` (path) - Role ID

**Response (200):**
```json
{
  "success": true,
  "message": "Role deleted successfully"
}
```

---

## Rank Endpoints

### List All Ranks
Get all ranks with their employees.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/ranks` | Get all ranks |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Junior",
      "employees": [...]
    }
  ]
}
```

---

### Create Rank
Create a new rank.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/ranks` | Create new rank |

**Request Body:**
```json
{
  "name": "Intern"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Rank created successfully",
  "data": {
    "id": 9,
    "name": "Intern"
  }
}
```

---

### Get Single Rank
Get details of a specific rank.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/ranks/{id}` | Get rank by ID |

**Parameters:**
- `id` (path) - Rank ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Junior",
    "employees": [...]
  }
}
```

---

### Update Rank
Update an existing rank.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/ranks/{id}` | Update rank by ID |

**Parameters:**
- `id` (path) - Rank ID

**Request Body:**
```json
{
  "name": "Senior Intern"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Rank updated successfully",
  "data": {
    "id": 9,
    "name": "Senior Intern"
  }
}
```

---

### Delete Rank
Permanently delete a rank.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/ranks/{id}` | Delete rank by ID |

**Parameters:**
- `id` (path) - Rank ID

**Response (200):**
```json
{
  "success": true,
  "message": "Rank deleted successfully"
}
```

---

## Position Endpoints

### List All Positions
Get all positions with their employees.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/positions` | Get all positions |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Software Engineer",
      "employees": [...]
    }
  ]
}
```

---

### Create Position
Create a new position.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/positions` | Create new position |

**Request Body:**
```json
{
  "name": "Data Scientist"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Position created successfully",
  "data": {
    "id": 16,
    "name": "Data Scientist"
  }
}
```

---

### Get Single Position
Get details of a specific position.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/positions/{id}` | Get position by ID |

**Parameters:**
- `id` (path) - Position ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Software Engineer",
    "employees": [...]
  }
}
```

---

### Update Position
Update an existing position.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/positions/{id}` | Update position by ID |

**Parameters:**
- `id` (path) - Position ID

**Request Body:**
```json
{
  "name": "Senior Data Scientist"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Position updated successfully",
  "data": {
    "id": 16,
    "name": "Senior Data Scientist"
  }
}
```

---

### Delete Position
Permanently delete a position.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/positions/{id}` | Delete position by ID |

**Parameters:**
- `id` (path) - Position ID

**Response (200):**
```json
{
  "success": true,
  "message": "Position deleted successfully"
}
```

---

## Common Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Not logged in yet"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

---

## Demo Credentials

For testing purposes, use these credentials:

- **Email:** admin@okr.com
- **Password:** password

---

## Notes

1. All datetime fields are returned in ISO 8601 format
2. All boolean values are returned as `true` or `false`
3. IDs are auto-incrementing integers
4. Foreign key relationships (rank_id, position_id, role_id) must reference existing records
5. Passwords are automatically hashed before storage
6. Soft delete is not implemented - delete operations permanently remove records
