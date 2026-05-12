# DepEd-ZC Inventory System

This is a Laravel-based inventory management system for DepEd-ZC.

## Prerequisites

Before setting up the project, ensure you have the following installed:
- **XAMPP** (for PHP and MySQL)
- **Composer** (PHP dependency manager)
- **Node.js & NPM** (for frontend assets)

## Installation & Setup

Follow these steps to initialize the project locally:

### 1. Open Terminal and Navigate to Project Directory
Open your terminal (in VS Code, press `Ctrl + ` `) and run:
```powershell
cd "C:\Users\Admin\DepEd-ZC_Inventory\DepEd-ZC_Inventory-System-main"
```

### 2. Install Dependencies
Install both PHP and JavaScript packages:
```powershell
composer install
npm install
```

### 3. Environment Configuration
If you don't have a `.env` file, create one from the template:
```powershell
copy .env.example .env
```
Then, generate the application security key:
```powershell
php artisan key:generate
```

### 4. Database Setup
1. Open **XAMPP Control Panel** and start **Apache** and **MySQL**.
2. Open [phpMyAdmin](http://localhost/phpmyadmin) in your browser.
3. Create a new database named `depedzc_inventory`.
4. Import the `inventory_system.sql` file (found in the root folder) into that database.
5. Ensure your `.env` file settings are correct:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=depedzc_inventory
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### 5. Running the Application
To run the system, you need to execute these two commands (ideally in separate terminal tabs):

**Terminal 1: Start the Backend Server**
```powershell
php artisan serve
```

**Terminal 2: Start the Frontend (Vite)**
```powershell
npm run dev
```

Once both are running, you can access the system at: `http://127.0.0.1:8000`
