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
      "position": "Chief Technology Officer"
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
      "position": "Chief Technology Officer",
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
  "position": "Software Engineer",
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
    "position": "Software Engineer",
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
    "position": "Chief Technology Officer",
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

## OKR Type Endpoints

### List All OKR Types
Get all OKR types.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okr-types` | Get all OKR types |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Individual",
      "is_employee": true
    },
    {
      "id": 2,
      "name": "Team",
      "is_employee": false
    },
    {
      "id": 3,
      "name": "Department",
      "is_employee": false
    },
    {
      "id": 4,
      "name": "Company",
      "is_employee": false
    }
  ]
}
```

---

### Create OKR Type
Create a new OKR type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/okr-types` | Create new OKR type |

**Request Body:**
```json
{
  "name": "Division",
  "is_employee": false
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "OKR type created successfully",
  "data": {
    "id": 5,
    "name": "Division",
    "is_employee": false
  }
}
```

---

### Get Single OKR Type
Get details of a specific OKR type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okr-types/{id}` | Get OKR type by ID |

**Parameters:**
- `id` (path) - OKR Type ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Individual",
    "is_employee": true,
    "okrs": [...]
  }
}
```

---

### Update OKR Type
Update an existing OKR type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/okr-types/{id}` | Update OKR type by ID |

**Parameters:**
- `id` (path) - OKR Type ID

**Request Body:**
```json
{
  "name": "Individual Contributor",
  "is_employee": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OKR type updated successfully",
  "data": {
    "id": 1,
    "name": "Individual Contributor",
    "is_employee": true
  }
}
```

---

### Delete OKR Type
Permanently delete an OKR type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/okr-types/{id}` | Delete OKR type by ID |

**Parameters:**
- `id` (path) - OKR Type ID

**Response (200):**
```json
{
  "success": true,
  "message": "OKR type deleted successfully"
}
```

---

## OKR Endpoints

### List All OKRs
Get all OKRs with optional filtering.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs` | Get all OKRs |

**Query Parameters:**
- `owner_type` (optional) - Filter by owner type (`App\Models\Employee` or `App\Models\OrgUnit`)
- `owner_id` (optional) - Filter by owner ID
- `okr_type_id` (optional) - Filter by OKR type ID
- `is_active` (optional) - Filter by active status (`true` or `false`)
- `employee_id` (optional) - Filter for specific employee's OKRs

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Q1 2026 Engineering Goals",
      "weight": 1.0,
      "okr_type_id": 1,
      "start_date": "2026-01-01T00:00:00.000000Z",
      "end_date": "2026-03-31T23:59:59.000000Z",
      "owner_type": "App\\Models\\Employee",
      "owner_id": 2,
      "is_active": true,
      "okrType": {
        "id": 1,
        "name": "Individual",
        "is_employee": true
      },
      "owner": { ... },
      "objectives": [ ... ]
    }
  ]
}
```

---

### Create OKR
Create a new OKR with optional objectives.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/okrs` | Create new OKR |

**Request Body:**
```json
{
  "name": "Q1 2026 Engineering Goals",
  "weight": 1.0,
  "okr_type_id": 1,
  "start_date": "2026-01-01",
  "end_date": "2026-03-31",
  "owner_type": "App\\Models\\Employee",
  "owner_id": 2,
  "is_active": true,
  "objectives": [
    {
      "description": "Complete the new user authentication module",
      "weight": 0.5,
      "target_type": "binary",
      "target_value": 1.0,
      "deadline": "2026-02-15",
      "tracking_type": "weekly",
      "tracker": 3,
      "approver": 4
    },
    {
      "description": "Achieve 85% code coverage",
      "weight": 0.5,
      "target_type": "numeric",
      "target_value": 85.0,
      "deadline": "2026-03-31",
      "tracking_type": "monthly",
      "tracker": 3,
      "approver": 4
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "OKR created successfully",
  "data": {
    "id": 1,
    "name": "Q1 2026 Engineering Goals",
    ...
  }
}
```

---

### Get Single OKR
Get details of a specific OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs/{id}` | Get OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Q1 2026 Engineering Goals",
    ...
  }
}
```

---

### Update OKR
Update an existing OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/okrs/{id}` | Update OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Request Body:**
```json
{
  "name": "Q1 2026 Engineering Goals Updated",
  "weight": 0.9,
  "okr_type_id": 1,
  "is_active": false
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OKR updated successfully",
  "data": { ... }
}
```

---

### Delete OKR
Permanently delete an OKR and its objectives.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/okrs/{id}` | Delete OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Response (200):**
```json
{
  "success": true,
  "message": "OKR deleted successfully"
}
```

---

### Activate OKR
Activate an OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/okrs/{id}/activate` | Activate OKR |

**Response (200):**
```json
{
  "success": true,
  "message": "OKR activated successfully",
  "data": { ... }
}
```

---

### Deactivate OKR
Deactivate an OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/okrs/{id}/deactivate` | Deactivate OKR |

**Response (200):**
```json
{
  "success": true,
  "message": "OKR deactivated successfully",
  "data": { ... }
}
```

---

### Get Available Owners
Get list of available employees and org units for OKR ownership.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs/available-owners` | Get available owners |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "employees": [
      {
        "id": 2,
        "title": "John Manager",
        "email": "john@example.com"
      }
    ],
    "org_units": [
      {
        "id": 1,
        "title": "Engineering Department",
        "custom_type": "Department"
      }
    ]
  }
}
```

---

## OrgUnit Endpoints

### List All Org Units
Get all organizational units.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunits` | Get all org units |

**Query Parameters:**
- `parent_id` (optional) - Filter by parent ID
- `orgunit_type_id` (optional) - Filter by org unit type ID
- `is_active` (optional) - Filter by active status

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Engineering Department",
      "custom_type": null,
      "orgunit_type_id": 1,
      "parent_id": null,
      "is_active": true,
      "type": { "id": 1, "name": "Department" },
      "parent": null
    }
  ]
}
```

---

### Get Org Unit Datatables
Get org units in datatables format.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunits/datatables` | Get org units for datatables |

**Query Parameters:**
- All datatables standard parameters (search, order, start, length, etc.)

**Response (200):**
```json
{
  "success": true,
  "data": [...]
}
```

---

### Create Org Unit
Create a new organizational unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/orgunits` | Create new org unit |

**Request Body:**
```json
{
  "name": "Marketing Department",
  "custom_type": null,
  "orgunit_type_id": 1,
  "parent_id": null,
  "is_active": true
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Org unit created successfully",
  "data": {
    "id": 8,
    "name": "Marketing Department",
    ...
  }
}
```

---

### Get Single Org Unit
Get details of a specific org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunits/{id}` | Get org unit by ID |

**Parameters:**
- `id` (path) - Org Unit ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Engineering Department",
    ...
  }
}
```

---

### Update Org Unit
Update an existing org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/orgunits/{id}` | Update org unit by ID |

**Parameters:**
- `id` (path) - Org Unit ID

**Request Body:**
```json
{
  "name": "Engineering & Technology Department"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit updated successfully",
  "data": { ... }
}
```

---

### Delete Org Unit
Permanently delete an org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/orgunits/{id}` | Delete org unit by ID |

**Parameters:**
- `id` (path) - Org Unit ID

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit deleted successfully"
}
```

---

### Activate Org Unit
Activate an org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/orgunits/{id}/activate` | Activate org unit |

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit activated successfully",
  "data": { ... }
}
```

---

### Deactivate Org Unit
Deactivate an org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/orgunits/{id}/deactivate` | Deactivate org unit |

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit deactivated successfully",
  "data": { ... }
}
```

---

### Get Org Unit Members
Get all members of a specific org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunits/{id}/members` | Get org unit members |

**Parameters:**
- `id` (path) - Org Unit ID

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "employee": { "id": 5, "name": "Jane Developer", ... },
      "orgunit_role": { "id": 1, "name": "Member" },
      "joined_at": "2026-01-01T10:00:00.000000Z"
    }
  ]
}
```

---

### Get Available Roles for Org Unit
Get available roles that can be assigned to org unit members.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunits/{id}/available-roles` | Get available roles for org unit |

**Parameters:**
- `id` (path) - Org Unit ID

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Head",
      "is_available": true
    }
  ]
}
```

---

### Add Member to Org Unit
Add an employee to an org unit with a specific role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/orgunits/{id}/members` | Add member to org unit |

**Parameters:**
- `id` (path) - Org Unit ID

**Request Body:**
```json
{
  "employee_id": 5,
  "orgunit_role_id": 1
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Member added successfully",
  "data": { ... }
}
```

---

### Update Org Unit Member Role
Change the role of a member in an org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/orgunits/{id}/members/{memberId}` | Update member role |

**Parameters:**
- `id` (path) - Org Unit ID
- `memberId` (path) - Member record ID

**Request Body:**
```json
{
  "orgunit_role_id": 2
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Member role updated successfully",
  "data": { ... }
}
```

---

### Remove Member from Org Unit
Remove a member from an org unit.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/orgunits/{id}/members/{memberId}` | Remove member from org unit |

**Parameters:**
- `id` (path) - Org Unit ID
- `memberId` (path) - Member record ID

**Response (200):**
```json
{
  "success": true,
  "message": "Member removed successfully"
}
```

---

## OrgUnit Type Endpoints

### List All Org Unit Types
Get all org unit types.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-types` | Get all org unit types |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Department",
      "org_units_count": 3
    }
  ]
}
```

---

### Create Org Unit Type
Create a new org unit type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/orgunit-types` | Create new org unit type |

**Request Body:**
```json
{
  "name": "Division"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Org unit type created successfully",
  "data": {
    "id": 5,
    "name": "Division"
  }
}
```

---

### Get Single Org Unit Type
Get details of a specific org unit type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-types/{id}` | Get org unit type by ID |

**Parameters:**
- `id` (path) - Org Unit Type ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Department",
    "org_units": [...]
  }
}
```

---

### Update Org Unit Type
Update an existing org unit type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/orgunit-types/{id}` | Update org unit type by ID |

**Parameters:**
- `id` (path) - Org Unit Type ID

**Request Body:**
```json
{
  "name": "Business Unit"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit type updated successfully",
  "data": { ... }
}
```

---

### Delete Org Unit Type
Permanently delete an org unit type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/orgunit-types/{id}` | Delete org unit type by ID |

**Parameters:**
- `id` (path) - Org Unit Type ID

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit type deleted successfully"
}
```

---

### Get OrgUnit Types Datatables
Get org unit types in datatables format.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-types/datatables` | Get org unit types for datatables |

**Query Parameters:**
- All datatables standard parameters (search, order, start, length, etc.)

**Response (200):**
```json
{
  "success": true,
  "data": [...]
}
```

---

## OrgUnit Role Endpoints

### List All Org Unit Roles
Get all org unit roles.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-roles` | Get all org unit roles |

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Head",
      "org_unit_employees_count": 2
    }
  ]
}
```

---

### Create Org Unit Role
Create a new org unit role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/orgunit-roles` | Create new org unit role |

**Request Body:**
```json
{
  "name": "Lead Developer"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Org unit role created successfully",
  "data": {
    "id": 4,
    "name": "Lead Developer"
  }
}
```

---

### Get Single Org Unit Role
Get details of a specific org unit role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-roles/{id}` | Get org unit role by ID |

**Parameters:**
- `id` (path) - Org Unit Role ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Head",
    "orgUnitEmployees": [...]
  }
}
```

---

### Update Org Unit Role
Update an existing org unit role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/orgunit-roles/{id}` | Update org unit role by ID |

**Parameters:**
- `id` (path) - Org Unit Role ID

**Request Body:**
```json
{
  "name": "Department Head"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit role updated successfully",
  "data": { ... }
}
```

---

### Delete Org Unit Role
Permanently delete an org unit role.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/orgunit-roles/{id}` | Delete org unit role by ID |

**Parameters:**
- `id` (path) - Org Unit Role ID

**Response (200):**
```json
{
  "success": true,
  "message": "Org unit role deleted successfully"
}
```

---

### Get OrgUnit Roles Datatables
Get org unit roles in datatables format.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/orgunit-roles/datatables` | Get org unit roles for datatables |

**Query Parameters:**
- All datatables standard parameters (search, order, start, length, etc.)

**Response (200):**
```json
{
  "success": true,
  "data": [...]
}
```

---

## CheckIn Endpoints

### List All Check-Ins
Get all check-ins with optional filtering.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/check-ins` | Get all check-ins |

**Query Parameters:**
- `objective_id` (optional) - Filter by objective ID

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "objective_id": 1,
      "date": "2026-01-15T10:00:00.000000Z",
      "current_value": 0.5,
      "comments": "Good progress this week.",
      "evidence_path": "/evidence/report.pdf",
      "created_at": "2026-01-15T10:00:00.000000Z",
      "updated_at": "2026-01-15T10:00:00.000000Z",
      "objective": {
        "id": 1,
        "description": "Complete OAuth2 integration"
      }
    }
  ]
}
```

---

### Create Check-In
Create a new check-in for an objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/check-ins` | Create new check-in |

**Request Body:**
```json
{
  "objective_id": 1,
  "date": "2026-01-20",
  "current_value": 75.5,
  "comments": "Ahead of schedule, good progress.",
  "evidence_path": "/evidence/jan20-report.pdf"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Check-in created successfully",
  "data": {
    "id": 7,
    "objective_id": 1,
    ...
  }
}
```

---

### Get Single Check-In
Get details of a specific check-in.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/check-ins/{id}` | Get check-in by ID |

**Parameters:**
- `id` (path) - Check-In ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "objective_id": 1,
    ...
  }
}
```

---

### Update Check-In
Update an existing check-in.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/check-ins/{id}` | Update check-in by ID |

**Parameters:**
- `id` (path) - Check-In ID

**Request Body:**
```json
{
  "current_value": 80.0,
  "comments": "Updated value after review."
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Check-in updated successfully",
  "data": { ... }
}
```

---

### Delete Check-In
Permanently delete a check-in.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/check-ins/{id}` | Delete check-in by ID |

**Parameters:**
- `id` (path) - Check-In ID

**Response (200):**
```json
{
  "success": true,
  "message": "Check-in deleted successfully"
}
```

---

### Get Check-Ins by Objective
Get all check-ins for a specific objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/check-ins/by-objective/{objectiveId}` | Get check-ins by objective ID |

**Parameters:**
- `objectiveId` (path) - Objective ID

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "objective_id": 1,
      "date": "2026-01-15T10:00:00.000000Z",
      "current_value": 0.5,
      ...
    }
  ]
}
```

---

### Get Check-Ins by Tracker
Get all check-ins for objectives where the user is the tracker.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/check-ins/by-tracker/{trackerId}` | Get check-ins by tracker ID |

**Parameters:**
- `trackerId` (path) - Employee ID (tracker)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "objective_id": 1,
      "date": "2026-01-15T10:00:00.000000Z",
      "current_value": 0.5,
      "objective": {
        "id": 1,
        "description": "Complete OAuth2 integration"
      }
    }
  ]
}
```

---

### List All OKRs
Get all OKRs with optional filtering.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs` | Get all OKRs |

**Query Parameters:**
- `owner_type` (optional) - Filter by owner type (`App\Models\Employee` or `App\Models\OrgUnit`)
- `owner_id` (optional) - Filter by owner ID
- `is_active` (optional) - Filter by active status (`true` or `false`)
- `employee_id` (optional) - Filter for specific employee's OKRs

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Q1 2026 Engineering Goals",
      "weight": 1.0,
      "start_date": "2026-01-01T00:00:00.000000Z",
      "end_date": "2026-03-31T23:59:59.000000Z",
      "owner_type": "App\\Models\\Employee",
      "owner_id": 2,
      "is_active": true,
      "owner": { ... },
      "objectives": [ ... ]
    }
  ]
}
```

---

### Create OKR
Create a new OKR with optional objectives.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/okrs` | Create new OKR |

**Request Body:**
```json
{
  "name": "Q1 2026 Engineering Goals",
  "weight": 1.0,
  "start_date": "2026-01-01",
  "end_date": "2026-03-31",
  "owner_type": "App\\Models\\Employee",
  "owner_id": 2,
  "is_active": true,
  "objectives": [
    {
      "description": "Complete the new user authentication module",
      "weight": 0.3,
      "target_type": "binary",
      "target_value": 1.0,
      "deadline": "2026-02-28",
      "tracking_type": "weekly",
      "tracker": 3,
      "approver": 4
    }
  ]
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "OKR created successfully",
  "data": {
    "id": 1,
    "name": "Q1 2026 Engineering Goals",
    ...
  }
}
```

---

### Get Single OKR
Get details of a specific OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs/{id}` | Get OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Q1 2026 Engineering Goals",
    ...
  }
}
```

---

### Update OKR
Update an existing OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/okrs/{id}` | Update OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Request Body:**
```json
{
  "name": "Q1 2026 Engineering Goals Updated",
  "weight": 0.9,
  "is_active": false
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "OKR updated successfully",
  "data": { ... }
}
```

---

### Delete OKR
Permanently delete an OKR and its objectives.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/okrs/{id}` | Delete OKR by ID |

**Parameters:**
- `id` (path) - OKR ID

**Response (200):**
```json
{
  "success": true,
  "message": "OKR deleted successfully"
}
```

---

### Activate OKR
Activate an OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/okrs/{id}/activate` | Activate OKR |

**Response (200):**
```json
{
  "success": true,
  "message": "OKR activated successfully",
  "data": { ... }
}
```

---

### Deactivate OKR
Deactivate an OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PATCH | `/okrs/{id}/deactivate` | Deactivate OKR |

**Response (200):**
```json
{
  "success": true,
  "message": "OKR deactivated successfully",
  "data": { ... }
}
```

---

### Get Available Owners
Get list of available employees and org units for OKR ownership.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/okrs/available-owners` | Get available owners |

**Response (200):**
```json
{
  "success": true,
  "data": {
    "employees": [
      {
        "id": 2,
        "title": "John Manager",
        "email": "john@example.com"
      }
    ],
    "org_units": [
      {
        "id": 1,
        "title": "Engineering Department",
        "custom_type": "Department"
      }
    ]
  }
}
```

---

## Objective Endpoints

### List All Objectives
Get all objectives with optional filtering.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/objectives` | Get all objectives |

**Query Parameters:**
- `okr_id` (optional) - Filter by OKR ID
- `tracker_id` (optional) - Filter by tracker employee ID
- `approver_id` (optional) - Filter by approver employee ID
- `target_type` (optional) - Filter by target type (`numeric` or `binary`)
- `tracking_type` (optional) - Filter by tracking type (`daily`, `weekly`, `monthly`, `quarterly`)
- `status` (optional) - Filter by status (`pending` or `completed`)

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "okr_id": 1,
      "description": "Complete the new user authentication module",
      "weight": 0.3,
      "target_type": "binary",
      "target_value": 1.0,
      "deadline": "2026-02-28T23:59:59.000000Z",
      "tracking_type": "weekly",
      "tracker": 3,
      "approver": 4,
      "okr": { ... },
      "trackerEmployee": { ... },
      "approverEmployee": { ... }
    }
  ]
}
```

---

### Create Objective
Create a new objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/objectives` | Create new objective |

**Request Body:**
```json
{
  "okr_id": 1,
  "description": "Complete the new user authentication module",
  "weight": 0.3,
  "target_type": "binary",
  "target_value": 1.0,
  "deadline": "2026-02-28",
  "tracking_type": "weekly",
  "tracker": 3,
  "approver": 4
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Objective created successfully",
  "data": {
    "id": 1,
    ...
  }
}
```

---

### Get Single Objective
Get details of a specific objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/objectives/{id}` | Get objective by ID |

**Parameters:**
- `id` (path) - Objective ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    ...
  }
}
```

---

### Update Objective
Update an existing objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT/PATCH | `/objectives/{id}` | Update objective by ID |

**Parameters:**
- `id` (path) - Objective ID

**Request Body:**
```json
{
  "description": "Updated description",
  "weight": 0.4
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Objective updated successfully",
  "data": { ... }
}
```

---

### Delete Objective
Permanently delete an objective.

| Method | Endpoint | Description |
|--------|----------|-------------|
| DELETE | `/objectives/{id}` | Delete objective by ID |

**Parameters:**
- `id` (path) - Objective ID

**Response (200):**
```json
{
  "success": true,
  "message": "Objective deleted successfully"
}
```

---

### Get Objectives by OKR
Get all objectives for a specific OKR.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/objectives/by-okr/{okrId}` | Get objectives by OKR ID |

**Parameters:**
- `okrId` (path) - OKR ID

**Response (200):**
```json
{
  "success": true,
  "data": {
    "okr": { ... },
    "objectives": [ ... ]
  }
}
```

---

### Get Objectives by Tracker
Get all objectives where the user is the tracker.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/objectives/by-tracker/{trackerId}` | Get objectives by tracker ID |

**Parameters:**
- `trackerId` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "data": [ ... ]
}
```

---

### Get Objectives by Approver
Get all objectives where the user is the approver.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/objectives/by-approver/{approverId}` | Get objectives by approver ID |

**Parameters:**
- `approverId` (path) - Employee ID

**Response (200):**
```json
{
  "success": true,
  "data": [ ... ]
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
4. Foreign key relationships (role_id) must reference existing records
5. Passwords are automatically hashed before storage
6. Soft delete is not implemented - delete operations permanently remove records
7. OKR weight must be between 0 and 1
8. Objective deadline must be before OKR end date
9. Tracking types: `daily`, `weekly`, `monthly`, `quarterly`
10. Target types: `numeric` (for measurable values), `binary` (for yes/no goals)

