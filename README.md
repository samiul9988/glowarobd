# Laravel eCommerce Website

A complete solution for E-commerce Business with exclusive features & super responsive layout. This project is a fully functional eCommerce platform built using the Laravel framework. Below, you'll find the necessary steps to install and set up the project on your local machine.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Run Application](#run-application)
- [Database](#database)
- [License](#license)

## Requirements

Before installation, ensure your system meets the following requirements:

- PHP >= 8.1
- Composer
- MySQL or any other supported database
- Git (optional)
- Laravel 8 or later

## Installation

### Step 1: Clone the Repository
```sh
git clone https://gitlab.com/rrakhmit/glowarobd.git
cd glowarobd
```

### Step 2: Checkout Branch
```sh
git checkout production
```

#### Optional
Create a new branch from the production branch and checkout to the new branch
```sh
git checkout -b new_branch_name
```

### Step 3: Install Dependencies
```sh
composer install
```

### Step 4: Configure Environment
```sh
cp .env.example .env
```
Edit the `.env` file and configure database details:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=database_name
DB_USERNAME=root
DB_PASSWORD=
```

### Step 5: Generate Application Key
```sh
php artisan key:generate
```

### Step 6: Storage & Permissions
```sh
php artisan storage:link
```

## Configuration
### Configure `.htaccess`
In the `public` directory, there is a file named `.htaccess-example`. Copy this file and rename it to `.htaccess`:
```sh
cp public/.htaccess-example public/.htaccess
```

## Serve Application
```sh
php artisan serve
```
Visit `http://localhost:8000/` in your browser.

### Setup Issue: View [frontend.index] Not Found

If you encounter this issue, follow these steps:

1. Navigate to:
   ```sh
   vendor\laravel\framework\src\Illuminate\Foundation\helpers.php
   ```
2. At the end of the `helpers.php` file, replace the `view` method with the following code:

```php
function view($view = null, $data = [], $mergeData = [])
{
    $factory = app(ViewFactory::class);

    if (func_num_args() === 0) {
        return $factory;
    }

    //return $factory->make($view, $data, $mergeData);
    if(strpos($view, 'otp_systems')!==false)
        return $factory->make($view, $data, $mergeData);
    else if(strpos($view, 'seller.pos')!==false)
        return $factory->make($view, $data, $mergeData);
    else if(strpos($view, 'frontend')!==false)
        return $factory->make(config('app.theme').$view, $data, $mergeData);
    else
        return $factory->make($view, $data, $mergeData);
}
```

## Database
[Click here](https://yourwebsite.com/path/to/database.sql) to download the database (.sql) file


## Admin Panel Access
Default admin credentials:
```
Email: admin@example.com
Password: password
```


## License
This project is available under the MIT License.

---
