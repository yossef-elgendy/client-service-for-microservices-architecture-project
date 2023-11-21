# Bachelor Project - Microservices for Nursery Search

## Overview

This project is a part of a microservices architecture designed to assist parents in finding suitable nurseries for their children. This Laravel service serves as the backend for the client-side application.

## Installation

### Prerequisites

Make sure you have [Composer](https://getcomposer.org/) installed on your machine.

### Steps

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/your-username/Bachelor-Project.git

2. **Navigate to the Project Directory:**

   ```bash
   cd Bachelor-Project

3. **Change To The Correct Branch:**

   ```bash
   git checkout elgendy

4. **Install Dependencies:**

   ```bash
   composer install

5. **Copy the Environment File:**

   ```bash
   cp .env.example .env

6. **Generate Application Key:**

   ```bash
    php artisan key:generate

7. **Configure Database:**

   ```bash
    DB_CONNECTION=mysql
    DB_HOST=your_database_host
    DB_PORT=your_database_port
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_username
    DB_PASSWORD=your_database_password

8. **Run Migrations and Seeders:**

   ```bash
    php artisan migrate --seed

9. **Start the Development Server:**

   ```bash
    php artisan serve

### Usage

Once the installation is complete, you can explore the API endpoints and integrate this service with the client-side application for nursery search functionality.
