# 🛠️ HelperCreator

HelperCreator is a Laravel package that makes it easy to manage and autoload custom helpers. It automates registration in `composer.json`, creates backups, and keeps your project clean and functional.

---

## 🚀 Features

-   ✅ Automatically registers helper files in the `autoload.files` key of `composer.json`
-   🧠 Creates and manages backups of the last 3 versions of `composer.json`
-   🧹 Smart command to clean up invalid entries
-   🔄 Restores backups safely
-   🧱 Compatible with Laravel 11+

---

## 📋 Requirements

-   PHP >= 8.1
-   Laravel >= 11.0

---

## 📦 Installation

```bash
composer require elisame/helper-creator
```

## ⚙️Settings

You can run the command to publish the settings file.

```
php artisan vendor:publish --tag=helper-creator-config
```

---

## ⚙️ Usage

### ✨ Creating a new helper

```
php artisan helper:create MyNewHelper
```

This will create the file in `app/Helpers` and automatically register it in `composer.json`.

### ♻️ Restore the last backup of composer.json

```
php artisan helper:restore-backup
```

Restores the previous version of `composer.json` and updates autoload.

## 🧹 Clean up invalid autoload entries

```
php artisan helper:cleanup
```

Removes files that were manually deleted but are still listed in composer.json. You can use the `--dry-run` flag to check what will be removed from the `composer.json` file without making any changes.

## 📁 Generated Structure

When you create a new helper, you will have the following structure inside your project root.

```
app/
└── Helpers/
└── MyNewHelper.php

composer.json
└── autoload.files
└── "app/Helpers/MyNewHelper.php"`
```

## 🛡️ Security

Before any changes to `composer.json`, the package creates automatic backups in:

```
storage/helper-creator/backups/composer
```

You can safely restore any previous version.
