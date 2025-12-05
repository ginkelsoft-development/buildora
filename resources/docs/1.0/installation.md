# Installation

---

- [Install via Composer](#composer)
- [Run the Installer](#installer)
- [Create Admin User](#admin-user)
- [Access the Panel](#access)

<a name="composer"></a>
## Install via Composer

```bash
composer require ginkelsoft/buildora
```

<a name="installer"></a>
## Run the Installer

```bash
php artisan buildora:install
```

The installer will:
- Publish configuration files
- Publish assets
- Run necessary migrations
- Optionally create an admin user

<a name="admin-user"></a>
## Create Admin User

If you didn't create one during installation:

```bash
php artisan buildora:user:create
```

<a name="access"></a>
## Access the Panel

Visit `/buildora/dashboard` in your browser and log in with your admin credentials.

> {primary} Congratulations! Buildora is now installed and ready to use.
