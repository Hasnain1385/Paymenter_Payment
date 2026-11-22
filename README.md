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
1. Download or locate `MultiBankQR.zip`.
2. Log into your **Paymenter Admin Panel**.
3. Navigate to **Extensions** ➜ click **Install Extension** (top right).
4. Drag and drop `MultiBankQR.zip` (or browse to select it).
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

### ⚙️ Admin Configuration
1. Log into your **Paymenter Admin Panel**.
2. Go to **Extensions ➜ Gateways** ➜ find **PakBank QR & Manual Verification** ➜ click **Enable**.
3. Fill in your details:
   * **WhatsApp Number**: e.g. `+923001234567` (include country code for direct WhatsApp click-to-chat).
   * **Discord Invite**: e.g. `https://discord.gg/yourserver`
   * **Bank Account 1 (Meezan / Raast)**: Account Title, IBAN / Raast ID, and image URL/path to your scanned static Raast QR code.
   * **Bank Account 2 (Easypaisa)**: Account Title, Mobile Number, and scanned Easypaisa QR screenshot image.
   * **Bank Account 3 (JazzCash)**: Account Title, Mobile Number, and scanned JazzCash QR screenshot image.
   * **Bank Account 4 (SadaPay / Bank Alfalah)**: Account Title, Number/IBAN, and scanned QR.
4. Click **Save Settings**.

---

## 🧪 Automated Testing

Run the included test suite to verify Pakistani account resolution, static QR image prioritization, status lifecycle, and input sanitization:

```bash
php tests/MultiBankQRTest.php
```

---

## 📄 License
Licensed under the [MIT License](LICENSE).
