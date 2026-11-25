# Running MVCLite-IAD Locally

This guide explains how to set up and run the **MVCLite-IAD** project on another device or access it through your local network (e.g., from a phone or another laptop).

---

## Prerequisites
Ensure you have the following installed on the target device:
*   **Docker & Docker Desktop** (Recommended for easiest setup)
*   *OR* **PHP 8.3+** and **MySQL/MariaDB** (Manual setup)

---

## Method 1: Using Docker (Recommended)
This method handles all dependencies, including the database and web server, automatically.

1.  **Clone/Copy** the project folder to the new device.
2.  **Open a Terminal** (CMD, PowerShell, or Bash) in the project root.
3.  **Start the containers**:
    ```bash
    docker compose up -d app
    ```
    If your Docker version uses the older command name, `docker-compose up -d app` is also fine.
4.  **Verify the app files are mounted correctly**:
    ```bash
    docker exec mvclite_app test -d /var/www/html/public && echo "public folder mounted"
    ```
    The Apache document root is `/var/www/html/public`. If this check fails or `/assets/...` files return 404, Docker is not seeing the full project folder. On Docker Desktop, check file sharing / drive access for the drive that contains this project, then recreate the app container.
5.  **Access the application**:
    *   On the same machine: [http://localhost:8001](http://localhost:8001)
    *   The database is initialized automatically using `pcaiad.sql`.

---

## Method 2: Manual PHP Server
If you prefer not to use Docker, you can use PHP's built-in development server.

1.  **Database Setup**:
    *   Create a MySQL database named `pcaiad`.
    *   Import the `pcaiad.sql` file into your database.
    *   Update `config/config.php` if your database credentials differ from the default (`test_user`/`password`).
2.  **Run the Server**:
    Open a terminal in the project root and run:
    ```bash
    php -S 127.0.0.1:8021 -t public dev-router.php
    ```
    The `dev-router.php` file serves real files from `public/` directly, then routes MVC URLs like `/admin/questionnaires` through `public/index.php`.

    > **Note**: Do not run the built-in server with `public/index.php` as the router. That sends `/assets/...` requests through the MVC router and can create false static asset 404s during Playwright testing.

    To allow other devices on your network to connect, use your machine's local IP or bind to `0.0.0.0`:
    ```bash
    php -S 0.0.0.0:8021 -t public dev-router.php
    ```

---

## Accessing from Other Devices (Local Network)
To view the website from a mobile phone, tablet, or another computer on the same Wi-Fi:

### 1. Find your Local IP Address (Host Machine)
*   **Windows**: Open CMD and type `ipconfig`. Look for "IPv4 Address" under your Wi-Fi or Ethernet adapter (e.g., `192.168.1.15`).
*   **Mac/Linux**: Open terminal and type `ifconfig` or `ip addr`.

### 2. Connect from another device
Ensure both devices are on the same Wi-Fi network. Open the browser on your phone/other device and enter:

*   **If using Docker**: `http://[YOUR_IP]:8001`
*   **If using PHP Server**: `http://[YOUR_IP]:8000`

> **Example**: `http://192.168.1.15:8001`

---

## Troubleshooting
*   **Firewall**: If you cannot connect, ensure your host machine's firewall allows incoming connections on ports **8000** or **8001**.
*   **Database Connection**: If you see a database error, double-check that the `DB_HOST` in `config/config.php` matches your setup (use `db` for Docker, `localhost` for manual).
*   **Apache/Nginx**: If deploying to a production-like environment, ensure the `DocumentRoot` points to the `/public` folder of the project.
*   **Static asset 404s**: Confirm `public/assets/css/style.css` loads in the browser. For Docker, verify `/var/www/html/public` exists inside `mvclite_app`. For PHP's built-in server, use `dev-router.php` as shown above.
*   **Private uploads**: Do not create a public `/uploads` route. Uploaded answer files should continue to use the app's authenticated download routes.
