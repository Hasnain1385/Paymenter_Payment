# MultiBank & Direct QR Pay — Paymenter Gateway Plugin

[![Paymenter](https://img.shields.io/badge/Paymenter-v1%20%7C%20v2-blue.svg)](https://paymenter.org)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.3%20%7C%208.4-777BB4.svg)](https://www.php.net)

A premium, high-converting payment gateway extension for **Paymenter** designed specifically to help hosting providers, agencies, and digital sellers **close deals** using multiple bank accounts, UPI, IBAN, Zelle, Wise, Revolut, and crypto with **interactive QR codes**, **1-click WhatsApp & Discord direct contact buttons**, and **real-time payment proof verification**.

---

## 💡 Why This Plugin Exists: Closing the Deal

When selling hosting or digital services, standard manual payment options often fail to convert customers:
* ❌ **Friction & Typos**: Customers get overwhelmed copying long IBANs or account numbers manually.
* ❌ **Unprofessional Experience**: Raw text instructions look amateurish and create trust hesitation.
* ❌ **Communication Gap**: Customers want immediate human reassurance before or right after transferring funds.
* ❌ **Uncertainty After Paying**: Customers wonder if their payment was received or when their service will be provisioned.

### How MultiBankQR Solves This:
1. **Fintech-Grade UI**: Polished, modern cards with SSL 256-bit encryption trust badges and bank brand tags.
2. **Interactive Multi-Account Switcher**: Tabbed bank switcher allowing customers to pick between Bank Transfer, Zelle, Wise, SEPA, UPI, or Crypto.
3. **High-Res Dynamic QR Codes**: Auto-generated SVG QR codes (or custom images) allowing instant scanning with mobile banking apps.
4. **1-Click Copy Buttons**: Single click to copy Account Title, Account Number/IBAN, Routing/SWIFT, and Reference Number with animated feedback.
5. **Direct Deal-Closing Buttons (WhatsApp & Discord)**:
   * **WhatsApp**: 1-click chat button with pre-filled message:
     `Hello! I have sent payment for Invoice #123 ($45.00). TRX ID: 8934710. Please verify and activate my order.`
   * **Discord**: Direct button to your Discord community or `#payment-proofs` ticket channel with helper guidance.
6. **Instant Proof Submission & Live Real-Time Polling**:
   * Customer enters their Transaction ID / UTR and attaches a receipt screenshot (with instant image preview).
   * The checkout screen immediately transforms into a live tracking status card.
   * Background AJAX polling runs every 5 seconds.
   * The moment you approve the transaction in the Paymenter Admin, the customer's screen celebrates with a green verified card and automatically redirects them to their active services!

---

## 📁 Directory Structure

```text
MultiBankQR/
├── composer.json                       # Package definition, PSR-4 autoloading & dependencies
├── database/
│   └── migrations/
│       ├── 2026_11_22_000001_create_multibank_accounts_table.php
│       └── 2026_11_22_000002_create_multibank_transactions_table.php
├── resources/
│   └── views/
│       ├── pay.blade.php               # Core interactive payment view (in-modal or embedded)
│       ├── standalone.blade.php        # Standalone full-page checkout view
│       └── filament/
│           └── view-proof-modal.blade.php # Admin receipt screenshot preview modal
├── routes/
│   └── web.php                         # Proof upload, live polling & admin verification routes
├── src/
│   ├── MultiBankQR.php                 # Main Paymenter Gateway Extension Class
│   ├── MultiBankQRServiceProvider.php  # Laravel Service Provider
│   ├── Filament/
│   │   └── Resources/
│   │       ├── MultiBankAccountResource.php     # Admin bank accounts manager
│   │       └── MultiBankTransactionResource.php # Admin proof verification dashboard
│   ├── Http/
│   │   └── Controllers/
│   │       ├── MultiBankController.php          # Customer checkout, submission & polling
│   │       └── MultiBankAdminController.php     # Admin approve / decline / flag endpoints
│   ├── Models/
│   │   ├── MultiBankAccount.php        # Account Eloquent model
│   │   └── MultiBankTransaction.php    # Transaction Eloquent model
│   └── Services/
│       ├── QrCodeService.php           # High-res SVG QR generator & URI builders
│       ├── BankAccountService.php      # Dynamic multi-account resolver & currency filter
│       └── PaymentApprovalService.php  # Automatic invoice settlement & service activation
├── storage/
│   └── multibank_proofs/               # Uploaded receipt storage
└── tests/
    └── MultiBankQRTest.php             # Full integration & unit test suite
```

---

## 🚀 Installation into Paymenter

### Step 1: Copy Extension to Paymenter

Copy or clone this directory into your Paymenter `extensions/Gateways/` folder:

```bash
# If your Paymenter is installed at /var/www/paymenter:
cp -r /path/to/Paymenter_Payment /var/www/paymenter/extensions/Gateways/MultiBankQR
```

*(Ensure the folder name is exactly `MultiBankQR`).*

### Step 2: Ensure Storage Permissions

Ensure the web server can write uploaded customer receipts:

```bash
cd /var/www/paymenter
chmod -R 775 storage bootstrap/cache extensions/Gateways/MultiBankQR
chown -R www-data:www-data storage extensions/Gateways/MultiBankQR
```

### Step 3: Clear Cache & Optimize Autoload

```bash
php artisan optimize:clear
composer dump-autoload -o
```

### Step 4: Enable & Configure in Admin Panel

1. Log into your **Paymenter Admin Panel**.
2. Navigate to **Extensions ➜ Gateways** (or **Payments ➜ Gateways**).
3. Find **MultiBank & Direct QR Pay** and click **Enable**.
4. Configure your details:
   * **WhatsApp Phone Number**: (e.g. `+1234567890` or `+923001234567`)
   * **Discord Invite Link**: (e.g. `https://discord.gg/yourserver`)
   * **Bank Accounts**: Configure your Primary Bank, Secondary Bank, Zelle, Wise, UPI, or Crypto.
   * **Click Save**.

---

## ⚙️ Configuration Guide

### 1. Contact & Closing Settings
| Setting Key | Description | Example |
| :--- | :--- | :--- |
| `whatsapp_number` | International phone number for 1-click WhatsApp button | `+1234567890` |
| `whatsapp_message` | Template with tokens `{invoice_id}`, `{amount}`, `{currency}`, `{trx_id}` | `Hello! I sent payment for Invoice #{invoice_id}...` |
| `discord_invite` | Discord Server or DM link | `https://discord.gg/mycommunity` |
| `discord_instructions` | Help note shown beneath Discord button | `Join #payment-proofs or open a ticket.` |
| `telegram_link` | Optional Telegram channel or username link | `https://t.me/myusername` |

### 2. Multi-Bank Accounts Configuration
You can configure up to 4 accounts directly in the flat admin panel fields:
* **Account 1 (Primary / Recommended)**: Bank Name, Holder Name, Account #/IBAN, Routing/SWIFT, QR Payload, Custom Instructions.
* **Account 2**: Secondary Bank / Wise / Revolut / Wire.
* **Account 3**: Mobile Wallet / UPI / CashApp / Binance Pay.
* **Account 4**: Regional / Local Bank.

#### Dynamic QR Tokens:
If you enter a dynamic QR payload, you can use:
* `{amount}`: The invoice payable amount.
* `{invoice_id}`: The invoice number/ID.

*Examples:*
* **UPI**: `upi://pay?pa=mybusiness@upi&pn=BusinessName&am={amount}&cu=INR&tn=Invoice-{invoice_id}`
* **Payment URL**: `https://mygateway.com/pay?ref={invoice_id}&sum={amount}`
* **Plain Account / Wallet**: `123456789012` or `0x71C...`

#### Advanced JSON Accounts:
If you need **unlimited bank accounts**, simply paste a JSON array in the `accounts_json` setting:
```json
[
  {
    "name": "Chase Bank (Zelle)",
    "badge": "Instant",
    "account_title": "Acme Corp LLC",
    "account_number": "payments@acme.com",
    "currency": "USD",
    "instructions": "Send via Zelle to payments@acme.com."
  },
  {
    "name": "Wise EUR Account",
    "badge": "SEPA Instant",
    "account_title": "Acme Corp LLC",
    "account_number": "BE12345678901234",
    "routing_code": "TRWIBEB1",
    "currency": "EUR"
  },
  {
    "name": "USDT (TRC20)",
    "badge": "Zero Fee",
    "account_title": "Crypto Wallet",
    "account_number": "TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE",
    "currency": "ALL"
  }
]
```

---

## 🛡️ Admin Verification Workflow

When a customer submits a transaction reference:
1. In the Paymenter Admin sidebar, under **Payments ➜ Bank QR Proofs**, a live **amber badge** appears showing the pending count.
2. Click on the transaction to review:
   * Invoice ID, Amount, Currency
   * Customer's Transaction ID / UTR
   * Sender Account Name
   * **View Proof Modal**: Full-size lightbox of customer receipt screenshot.
3. **Actions**:
   * **✅ Approve & Mark Paid**: Instantly marks the invoice as **PAID** in Paymenter, triggers automatic service provisioning, and completes the order.
   * **⚠️ Flag Issue**: Enter a note (e.g. *"Short payment received: $40 instead of $50"*). The customer's screen updates in real time to show your note with a re-upload button.
   * **❌ Decline**: Marks the transaction declined with a custom explanation.

---

## 🧪 Testing

Run the built-in test suite anytime:

```bash
php tests/MultiBankQRTest.php
```

---

## 📄 License

This extension is licensed under the [MIT License](LICENSE).
Built with ❤️ for the Paymenter community.
