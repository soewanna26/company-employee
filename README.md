Project: Company & Employee Management System
Hello,

This repository contains a Laravel-based Company and Employee Management System, which includes the following functionalities:

Features Implemented:
1. Authentication:

Basic Authentication has been implemented using Laravel Breeze.
Role-Based Access Control (RBAC) is integrated with Spatie Role & Permission, ensuring that only users with the SuperAdmin role can manage companies and employees.

2. User Seeder:

A UserSeeder has been added to seed the database with an initial SuperAdmin user for testing and setup purposes.
Email: admin@admin.com
Password: password
Role: SuperAdmin

3. CRUD Operations:

Company and Employee models support CRUD operations.
Companies have the following fields: name, email, logo, website.
Employees have the following fields: name, email, phone, profile, company_id.

4. File Handling:

Logos and profile images are stored in the storage/app/public directory.
The Intervention Image library is used to resize uploaded logos and ensure they meet minimum size requirements.

5. API Endpoints:

All necessary API endpoints for managing companies and employees have been completed. The API supports:
GET /api/companies, POST /api/companies, PUT /api/companies/{id}, DELETE /api/companies/{id}
GET /api/employees, POST /api/employees, PUT /api/employees/{id}, DELETE /api/employees/{id}
The API uses API Token Authentication (via Laravel Passport or Sanctum).

6. Search & Filtering:

Search and filtering functionalities are implemented for the Companies and Employees lists.
Companies can be searched by name, email, and website.
Employees can be searched by name, email, and phone.

7. Pagination:

Pagination is applied to both the Companies and Employees lists, and it works seamlessly with the search and filter functionalities.

8. Role-Based Dashboard:

A simple dashboard has been created for administrators, displaying:
The total number of companies and employees.
A list of the most recently added companies.

Apology & Explanation:
I would like to sincerely apologize for not completing the tests as part of this project. Unfortunately, due to time constraints, I had to prioritize another project. Although the tests were left incomplete, all the core features, including the API endpoints, authentication, file handling, and CRUD functionality, are fully implemented and functional.

This is my git
https://github.com/soewanna26/company-employee
