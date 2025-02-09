# Tracker Backend  ![Tests](https://github.com/matheuskolln/tracker-backend/actions/workflows/laravel-ci.yml/badge.svg)
Tracker Backend is a RESTful API built with Laravel to manage tasks and track time efficiently. The API enables users to create, assign, and monitor tasks while keeping track of time spent on each activity.

## Architecture

The project follows a **Clean Architecture** approach, separating concerns into different layers:

- **Controllers**: Handle HTTP requests and responses.
- **Services**: Contain business logic and coordinate operations between repositories and models.
- **Repositories**: Handle database interactions using Laravel Eloquent.
- **Models**: Represent database entities and define relationships.
- **Middleware**: Manage request filtering and authentication.
- **Routes**: Define API endpoints and request handling.

## Features

- **Task Management**: Create, update, and delete tasks.
- **Task Assignment**: Assign tasks to specific users.
- **Time Tracking**: Monitor time spent on each task.
- **User Authentication**: Secure login and access control using JWT.
- **Role-Based Access Control (RBAC)**: Define different user roles and permissions.

## Technologies Used

- **Framework**: [Laravel](https://laravel.com/)
- **Programming Language**: PHP
- **Database**: MySQL or PostgreSQL
- **Dependency Management**: Composer
- **Authentication**: Laravel Sanctum (JWT-based)
- **Testing**: PHPUnit

## Prerequisites

Ensure you have the following installed:

- **PHP**: Version 7.4 or higher
- **Composer**: Version 2.0 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 9.6+

## Installation

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/matheuskolln/tracker-backend.git
   cd tracker-backend
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   - Copy the example environment file:
     ```bash
     cp .env.example .env
     ```
   - Update the `.env` file with your database credentials and other configurations.

4. **Generate the Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations and Seed Data**:
   ```bash
   php artisan migrate --seed
   ```

6. **Start the Development Server**:
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`.

## Usage

Once the server is running, you can interact with the API using tools like [Postman](https://www.postman.com/) or [Insomnia](https://insomnia.rest/). Authentication is required for most endpoints, so make sure to include a valid JWT token in the request headers.

### Example Endpoints

- **User Login**:
  ```http
  POST /api/login
  ```
  **Payload**:
  ```json
  {
    "email": "user@example.com",
    "password": "password123"
  }
  ```

- **Create a Task**:
  ```http
  POST /api/tasks
  ```
  **Payload**:
  ```json
  {
    "title": "New Task",
    "description": "Description of the task",
  }
  ```

## Testing

Run the test suite with:
```bash
php artisan test
```

## Contribution

Contributions are welcome! Feel free to open issues or submit pull requests. Please follow the contribution guidelines when collaborating on the project.


## Contact

For questions or support, contact [Matheus Kolln](https://github.com/matheuskolln).

