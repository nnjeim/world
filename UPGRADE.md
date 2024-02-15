# Upgrade

## 1.1.29 to 1.1.30

### Update Configuration

To ensure compatibility with the latest version, add a 'connection' entry in the `config/world.php` file. You have two options to do this:

#### Option 1: Manual Configuration

Open the `config/world.php` file and insert the following code snippet:

```php
/*
|--------------------------------------------------------------------------
 Connection
|--------------------------------------------------------------------------
*/
'connection' => env('WORLD_DB_CONNECTION', env('DB_CONNECTION')),
```

#### Option 2: Republish Configuration

Alternatively, you can republish the configuration file using the following Artisan command:

```bash
php artisan vendor:publish --tag=world --force
```

However, be aware that this command will overwrite any previous changes made to the configuration file.