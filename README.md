# Secure Password Manager (Passman) - Security Audit & Remediation

**Course:** Information Systems Security (Auth-ECE)  
**Author:** Nikos Toulkeridis  
**Date:** December 2025  

## 📌 Project Overview

This project is part of a university assignment focused on auditing and securing a vulnerable legacy PHP/MySQL web application named **'passman'**. 

The original application was intentionally flawed, containing critical security vulnerabilities commonly found in legacy web systems. The goal of this project was to identify these vulnerabilities, document them, and refactor the source code to implement robust security controls without altering the core functionality.

## 🛠️ Vulnerability Analysis & Applied Solutions

Per the assignment instructions, 5 specific security domains were addressed. Below is a summary of the vulnerabilities found and the remediation strategies applied.

### 1. Database Privileges (Least Privilege)
* **Vulnerability:** The application originally connected to the database using the `root` user with an empty password. This granted excessive privileges, risking the entire database server in the event of an injection attack.
* **Solution:** * Created a dedicated database user `sec_user` with a strong password.
    * Granted strictly limited permissions (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) only on the specific database `pwd_mgr`.
    * **Files Modified:** `login.php`, `register.php`, `dashboard.php`, `notes.php`.

### 2. SQL Injection (SQLi)
* **Vulnerability:** User input was concatenated directly into SQL query strings. This allowed Authentication Bypass (e.g., `' OR '1'='1`) and arbitrary data manipulation.
* **Solution:** * Refactored all database interactions to use **MySQLi Prepared Statements**.
    * Inputs are now bound as parameters (`bind_param`), separating code from data and neutralizing injection attacks.
    * **Files Modified:** `login.php` (Authentication), `register.php` (User Creation), `dashboard.php` (CRUD operations).

### 3. Stored Cross-Site Scripting (XSS)
* **Vulnerability:** The `notes.php` page echoed user input directly to the browser without sanitization. This allowed attackers to inject malicious JavaScript (as demonstrated with the cookie stealing scripts in the `xss/` folder).
* **Solution:** * Applied **Output Encoding** using the `htmlspecialchars()` function before rendering data. 
    * This converts special characters (like `<script>`) into harmless HTML entities.
    * **Files Modified:** `notes.php`.

### 4. Insecure Password Storage
* **Vulnerability:** User passwords were stored in the `login_users` table in **Plaintext**. A database leak would instantly compromise all user credentials.
* **Solution:** * Implemented strong hashing using the **Bcrypt** algorithm.
    * Used `password_hash()` during registration and `password_verify()` during login.
    * **Files Modified:** `register.php`, `login.php`.

### 5. Insecure Communication (HTTP)
* **Vulnerability:** The application operated over HTTP, transmitting sensitive data (passwords, session cookies) in clear text, making it vulnerable to Man-in-the-Middle (MitM) attacks.
* **Solution:** * Implemented Application-Layer defenses to enforce security headers.
    * Added logic to force **HTTPS Redirection**.
    * Configured **Secure Session Cookies** with `HttpOnly`, `Secure`, and `SameSite=Strict` flags to prevent session hijacking via XSS or sniffing.
    * **Files Modified:** `login.php` (and recommended for all session-start points).

---

## 🚀 Setup & Installation (Localhost)

1.  **Environment:** Ensure you have XAMPP (Apache + MySQL) installed.
2.  **Database:** * Import the `pwd_mgr` database structure.
    * Create the `sec_user` as described in the report.
3.  **Deploy:** Place the source files in `htdocs/passman`.
4.  **Run:** Navigate to `http://localhost/passman/index.html`.

## ⚠️ Disclaimer

This code is for educational purposes as part of the "Information Systems Security" course. The `xss/` folder contains vulnerable payloads used strictly for demonstration within a controlled local environment.
