<p align="center">
  <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" width="128" height="128" alt="PakBank QR Logo" style="border-radius: 28px;">
</p>

# PakBank QR & Manual Payment Gateway for Paymenter

A dedicated payment gateway plugin for **Paymenter** designed for **Pakistani payments** (Raast P2P/P2M, Meezan Bank, Easypaisa, JazzCash, SadaPay, NayaPay, Bank Alfalah, and commercial banks).

It allows merchants to display **scanned static QR codes** of their accounts, provides **pre-payment & post-payment WhatsApp contact buttons**, links to **Discord**, transitions invoices through a transparent **Pending Payment ➜ Pending Verification ➜ Verified** lifecycle, and includes enterprise security hardening against SQL Injection, XSS, and arbitrary file execution.

---

## 🌟 Key Features

### 1. Pakistani Payment Methods & Scanned Static QRs
* **Built-in Presets for Pakistani Rails**:
  * **Raast P2P / Commercial Banks** (Meezan Bank, HBL, Bank Alfalah, etc. with IBAN / Raast ID)
  * **Easypaisa Mobile Wallet** (Mobile Number & Scanned QR)
  * **JazzCash Mobile Wallet** (Mobile Number & Scanned QR)
  * **SadaPay / NayaPay / Digital Wallets**
* **Scanned Static QR Code Support**:
  * Display the merchant's exact scanned account QR image (uploaded from mobile banking apps).
  * Interactive **Click to Enlarge / Zoom Lightbox** modal.
* **1-Click Copy Details**:
  * Fast 1-click copy with animated feedback for Account Title, Account Number/IBAN/Raast ID, Bank Name, and Transfer Remarks (`INV-123`).

### 2. Pakistani E-Commerce Style WhatsApp & Discord Buttons
* **Pre-Payment WhatsApp Chat Button ("Questions Before Paying?")**:
  * Enables hesitant customers to contact you *before transferring money*.
  * Pre-fills a message:
    `Assalam-o-Alaikum! I have a question regarding Invoice #123 (PKR 5,000.00) before making payment. Can you please assist me?`
* **Post-Payment WhatsApp Submission Button ("Send Proof on WhatsApp")**:
  * Formatted message with customer transaction details:
    `Assalam-o-Alaikum! I have transferred PKR 5,000.00 for Invoice #123. TRX ID: 1928374829, Sender Name: Ali. Please manually verify and activate my order.`
* **Discord Community & Ticket Support**:
  * Direct button linking to your Discord server or `#payment-proofs` ticket channel.

### 3. Clear Status Lifecycle
* **Stage 1 — Pending Payment**:
  * Invoice is created and awaiting transfer.
  * Screen displays bank account details, scanned QR, and pre-payment WhatsApp help button.
* **Stage 2 — Pending Verification**:
  * Triggered immediately when the customer enters their Transaction ID (TRX/UTR) and optional receipt screenshot.
  * Screen updates to an amber badge with an animated radar: `Status: Pending Verification`.
  * Background polling runs every 5 seconds.
* **Stage 3 — Verified**:
  * Once the administrator clicks **Approve** in the admin panel, the customer's page automatically turns green:
    `✅ Status: Verified & Approved!`
  * Automatically redirects the customer to their active service dashboard.

---

## 🛡️ Security Audit & Hardening

| Threat Vector | Mitigation Strategy Implemented |
| :--- | :--- |
| **SQL Injection (SQLi)** | 100% Parameterized queries using Laravel Eloquent ORM. No raw query concatenation. Strict integer casting for route IDs (`(int) $id`). |
| **Cross-Site Scripting (XSS)** | All user inputs are sanitized with `strip_tags()` and encoded with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. Blade templates escape outputs using `{{ ... }}`. JavaScript copies safely use `textContent`. |
| **PHP File Upload Attacks** | Client-provided extensions and headers are untrusted. File uploads undergo server-side MIME type inspection via `finfo_file` (allowing only `image/jpeg`, `image/png`, `image/webp`, and `application/pdf`). Filenames are randomized with `Str::random(32)`. Max size capped at 5MB. |
| **Script Execution in Storage** | The receipt directory is secured via `.htaccess` to block execution of `.php`, `.phtml`, `.phar`, `.cgi`, or executable scripts even if uploaded. |
| **IDOR & Unauthorized Access** | Admin actions (`approve`, `decline`, `flag`) enforce strict authentication and admin role verification in `MultiBankAdminController.php`. Customer checkout enforces invoice ownership checks. |
| **CSRF & Rate Limiting** | Form submissions require `@csrf` tokens. Routes are throttled via Laravel middleware (`throttle:15,1` for submissions, `throttle:120,1` for polling). |

---

## 🚀 Installation & Setup

### Method 1: Upload via Paymenter Admin UI (Recommended)
1. Download this repository as a ZIP from GitHub (**Code ➜ Download ZIP**).
2. Log into your **Paymenter Admin Panel**.
3. Navigate to **Extensions** ➜ click **Install Extension** (top right).
4. Drag and drop the downloaded ZIP file (or browse to select it).
5. Paymenter will automatically extract the files directly to `extensions/Gateways/MultiBankQR` and register the extension.

---

### Method 2: Manual Installation via Terminal
1. Clone or copy this repository into your Paymenter root directory under `extensions/Gateways/MultiBankQR`:

```bash
mkdir -p /var/www/paymenter/extensions/Gateways/MultiBankQR
cp -r ./* /var/www/paymenter/extensions/Gateways/MultiBankQR/
```

2. Set file permissions:
```bash
cd /var/www/paymenter
chmod -R 775 extensions/Gateways/MultiBankQR storage
chown -R www-data:www-data extensions/Gateways/MultiBankQR storage
```

3. Clear Caches & optimize:
```bash
php artisan optimize:clear
composer dump-autoload -o
```

---

### ⚙️ Admin Configuration & Access

You can manage bank accounts and verify customer payments through **three convenient methods**:

#### Method 1: Native Paymenter Admin Sidebar Navigation
When the extension is enabled, Paymenter automatically registers and displays two dedicated management resources directly in the admin sidebar menu under the **Payments** navigation group:
* **🏦 Bank Accounts & QRs**: Add new accounts via modal popup, edit details, upload static QR images, toggle active status, and reorder.
* **💳 Manual Bank Proofs**: View submitted proofs, inspect uploaded receipt images, and 1-click Approve or Decline transactions.

#### Method 2: Gateway Configuration Form (In-Place Settings)
1. Navigate to **Extensions ➜ Gateways ➜ PakBank QR & Manual Verification ➜ Edit**.
2. Configure your bank accounts directly on the page:
   * **Account 1 (Primary / Raast)**: Bank name, account number/IBAN, title, static QR URL, instructions.
   * **Account 2 (Easypaisa Wallet)**: Name, mobile number, title, static QR URL.
   * **Account 3 (JazzCash Wallet)**: Name, mobile number, title, static QR URL.
   * **Account 4 (Digital Bank / Custom)**: Name, number, title, static QR URL.
3. Configure your Support **WhatsApp number**, **Discord invite link**, and **Payment notice**.
4. Click **Save Settings**. (Settings automatically synchronize with the database on save).

#### Method 3: Dedicated Fullscreen Dashboards
Direct browser links with dark-mode optimized interfaces:
* **Bank Accounts Manager**: `/admin/multibankqr/accounts`
* **Payment History & Verification**: `/admin/multibankqr/transactions`
* **GitHub In-Place Updates & Sync**: `/admin/multibankqr/updates`

---

### 💳 Payment History & 1-Click Verification Dashboard
Navigate to: **`/admin/multibankqr/transactions`**
* **Real-time Overview Metrics**: View Pending Approval count (with live animation), Total Verified Revenue, and Completed Orders.
* **Filter & Search**: Quickly filter by `Pending Verification`, `Verified`, or `Declined` status, and search by Invoice #, TRX/UTR ID, Customer Name, or Bank Name.
* **Inspect Proof Receipts**: 1-click preview lightbox for uploaded payment screenshots and receipts.
* **1-Click Approve / Decline**: Instantly mark transactions as approved, which updates the invoice status to PAID and alerts the customer via live status polling, or decline invalid submissions with an optional reason.

---

### 🏦 Dynamic Bank Accounts Manager (Add / Edit / Remove Accounts)
Navigate to: **`/admin/multibankqr/accounts`**
* **Add Unlimited Accounts**: Add Pakistani bank accounts (Meezan, HBL, Alfalah, Standard Chartered), Raast IDs, or mobile wallets (Easypaisa, JazzCash, SadaPay, NayaPay).
* **Attach Scanned Static QR Codes**: Upload mobile banking QR code screenshots or enter direct URLs.
* **On/Off Toggles**: Instantly enable or disable any bank account without removing it.
* **Delete / Remove Accounts**: 1-click removal of outdated accounts with instant confirmation.
* **Import from Gateway Settings**: 1-click button to automatically import legacy `acc1..acc4` slots into the database.

---

### 🔄 In-Place GitHub Updates & Push Sync
Navigate to: **`/admin/multibankqr/updates`**
* **Check for Updates**: Queries GitHub (`Hasnain1385/Paymenter_Payment`) to compare your installed version against the latest releases, tags, and commits.
* **⚡ Update In-Place**: 1-click updates the extension directly on your server without needing to re-upload ZIP archives. If installed via Git, it automatically runs `git pull`; if installed via ZIP, it safely updates the codebase in-place while preserving customer uploads.
* **🚀 Push to GitHub In-Place**: When making modifications on a server or development clone, commit and push your changes directly back to GitHub from the admin UI with a custom commit message.

---

## 🧪 Automated Testing

Run the included test suite to verify Pakistani account resolution, static QR image prioritization, status lifecycle, and input sanitization:

```bash
php tests/MultiBankQRTest.php
```

---

## 📄 License
Licensed under the [MIT License](LICENSE).
