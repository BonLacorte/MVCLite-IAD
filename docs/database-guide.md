# Local Database Operations Guide

This guide provides step-by-step instructions for interacting with, querying, and managing your containerized MariaDB database (`mvclite_db`) in the MVCLite-IAD development environment.

---

## 1. Quick Connection Settings

Your local environment runs a MariaDB container that is exposed to your host machine. Use these credentials to connect:

| Parameter | Value | Description |
| :--- | :--- | :--- |
| **Database Engine** | MariaDB (MySQL compatible) | Standard SQL Engine |
| **Host Name** | `localhost` | For host machine tools (e.g., local PHP server, DBeaver) |
| **External Port** | `3307` | Exposed on your host machine |
| **Internal Port** | `3306` | Port used inside the Docker network |
| **Database Name** | `pcaiad` | Main schema |
| **User Name** | `test_user` | Non-root developer account |
| **Password** | `password` | Password for `test_user` |
| **Root Password** | `root_password` | Full administrator database password |

---

## 2. Checking Database Contents via Terminal

You can run SQL commands directly inside the running container using the command line.

### Step 1: Open the Database Container CLI Shell
To start an interactive database session inside the container, open your PowerShell or command prompt and run:

```bash
docker exec -it mvclite_db mysql -u test_user -ppassword pcaiad
```

> [!NOTE]
> * `-it` starts an interactive terminal.
> * `pcaiad` at the end selects the database automatically so you don't have to write `USE pcaiad;`.

### Step 2: Basic Navigation Commands
Once you are in the MariaDB CLI shell (indicated by the `MariaDB [pcaiad]>` prompt), run these commands:

* **Show all tables:**
  ```sql
  SHOW TABLES;
  ```
* **View the structure (columns and types) of a table:**
  ```sql
  DESCRIBE users;
  -- or
  DESCRIBE events;
  ```
* **Exit the database session:**
  ```sql
  exit;
  ```

---

## 3. Basic CRUD Operations (Adding, Editing, Deleting)

Here are tailored SQL examples for the `users` and `events` tables in your application.

### A. Reading Data (SELECT)
* **View all registered users:**
  ```sql
  SELECT id, name, email, role, position FROM users WHERE is_deleted = 0;
  ```
* **View the 5 most recent active events:**
  ```sql
  SELECT id, title, venue, date, time, status FROM events WHERE is_deleted = 0 ORDER BY date DESC LIMIT 5;
  ```
* **Count how many active events exist:**
  ```sql
  SELECT COUNT(*) AS total_active_events FROM events WHERE is_deleted = 0;
  ```

### B. Adding Data (INSERT)
* **Insert a new user (default role is 'user'):**
  ```sql
  INSERT INTO users (name, email, password, role, position, createdTime) 
  VALUES ('John Doe', 'john@example.com', 'hashed_password_here', 'user', 'Technical Support Officer', NOW());
  ```
* **Insert a new event:**
  ```sql
  INSERT INTO events (title, venue, date, time, created_by, status) 
  VALUES ('IAD Performance Assessment Review', 'PCA Board Room', '2026-06-15', '09:00:00', 8, 'active');
  ```

### C. Editing Data (UPDATE)
* **Change a user's role to administrator:**
  ```sql
  UPDATE users 
  SET role = 'admin', edited_at = NOW() 
  WHERE email = 'john@example.com';
  ```
* **Reschedule an event (changing date, time, and venue):**
  ```sql
  UPDATE events 
  SET date = '2026-06-18', time = '10:30:00', venue = 'Agrohub Building Day 2 Room' 
  WHERE id = 20;
  ```
* **Finish an active event:**
  ```sql
  UPDATE events 
  SET status = 'finished' 
  WHERE id = 20;
  ```

### D. Deleting Data (DELETE)
Your database structure supports both **Soft Deleting** (marking as deleted without destroying records) and **Hard Deleting** (permanently removing records).

* **Soft Delete an event (Recommended for application integrity):**
  ```sql
  UPDATE events 
  SET is_deleted = 1 
  WHERE id = 20;
  ```
* **Soft Delete a user:**
  ```sql
  UPDATE users 
  SET is_deleted = 1, edited_at = NOW() 
  WHERE id = 12;
  ```
* **Hard Delete a record (Permanently removes from storage):**
  ```sql
  DELETE FROM events 
  WHERE id = 20 AND is_deleted = 1;
  ```

---

## 4. Connecting via External GUI Tools (DBeaver, TablePlus, HeidiSQL)

Using a database GUI is highly recommended for productivity. It allows you to inspect tables, browse data, and write queries in a visual editor.

### Connection Steps:
1. Open your GUI Client of choice (e.g., [DBeaver](https://dbeaver.io/) or [TablePlus](https://tableplus.com/)).
2. Select **Create New Connection** and choose **MySQL** or **MariaDB**.
3. Fill in the following connection settings:
   * **Host:** `localhost` or `127.0.0.1`
   * **Port:** `3307` *(Crucial: do not use 3306, as 3307 is mapped to your container)*
   * **Database:** `pcaiad`
   * **Username:** `test_user`
   * **Password:** `password`
4. Click **Test Connection** to verify. If it succeeds, save the connection and enjoy visual database access!

---

## 5. Backup & Import Recipes

### A. Backing Up the Database (Exporting to `.sql`)
To capture a full backup of the current database state from the container and save it to your host's workspace:

```bash
docker exec mvclite_db mysqldump -u test_user -ppassword pcaiad > pcaiad_backup.sql
```

### B. Restoring the Database (Importing `.sql`)
If your container gets recreated or you run into Windows/WSL path mounting permissions, follow this foolproof 2-step direct-import method:

1. **Copy the backup file directly into the container filesystem:**
   ```bash
   docker cp pcaiad.sql mvclite_db:/tmp/pcaiad.sql
   ```
2. **Execute the SQL file from inside the container:**
   ```bash
   docker exec -i mvclite_db mysql -u test_user -ppassword pcaiad -e "source /tmp/pcaiad.sql"
   ```

---

## 6. Troubleshooting Tips

### Error: `No connection could be made because the target machine actively refused it`
* **Cause:** The database container is stopped, or the container was not started with the correct port settings.
* **Solution:** Open your terminal in the project root and restart the containers to load the ports:
  ```bash
  docker-compose down
  docker-compose up -d
  ```

### Error: `Base table or view not found: 1146 Table 'pcaiad.events' doesn't exist`
* **Cause:** Docker Desktop ran into Windows drive-sharing issues (often on drive `E:`), resulting in the volume starting empty without importing the init script.
* **Solution:** Copy the `pcaiad.sql` file and import it manually using the [Restoring the Database](#b-restoring-the-database-importing-sql) instructions above.
