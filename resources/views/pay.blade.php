@php
    $invoiceId = (int) ($invoice->id ?? 0);
    $invoiceTotal = number_format((float) ($total ?? ($invoice->remaining ?? $invoice->total)), 2);
    $invoiceCurrency = htmlspecialchars($currency ?? ($invoice->currency_code ?? ($invoice->currency ?? 'PKR')), ENT_QUOTES, 'UTF-8');
    $hasTransaction = !empty($transaction);
    $isPendingVerif = $hasTransaction && $transaction->isPendingVerification();
    $isVerified = $hasTransaction && $transaction->isVerified();
    $isFlagged = $hasTransaction && $transaction->isFlagged();
    $isDeclined = $hasTransaction && $transaction->isDeclined();
@endphp

<div class="mb-checkout-root">
    <style>
        /* Scoped Self-Contained Theme Variables - Clean & Crisp in Light & Dark Modes */
        .mb-checkout-root,
        html:not(.dark) .mb-checkout-root,
        body:not(.dark) .mb-checkout-root,
        .light .mb-checkout-root,
        [data-theme="light"] .mb-checkout-root,
        [data-bs-theme="light"] .mb-checkout-root {
            --mb-bg: #ffffff;
            --mb-card: #f8fafc;
            --mb-card-hover: #f1f5f9;
            --mb-border: #e2e8f0;
            --mb-border-active: #10b981;
            --mb-text: #0f172a;
            --mb-text-muted: #64748b;
            --mb-input-bg: #ffffff;
            --mb-input-border: #cbd5e1;
            --mb-chip-bg: #f1f5f9;
            --mb-chip-text: #334155;
            --mb-qr-bg: #ffffff;
            --mb-qr-border: #e2e8f0;
            --mb-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-family: var(--mb-font);
            color: var(--mb-text);
            max-width: 680px;
            margin: 0 auto;
            padding: 4px;
        }

        /* Dark Mode: Activated strictly when Paymenter or parent container has .dark class */
        html.dark .mb-checkout-root,
        body.dark .mb-checkout-root,
        .dark .mb-checkout-root,
        [data-theme="dark"] .mb-checkout-root,
        [data-bs-theme="dark"] .mb-checkout-root {
            --mb-bg: #0f172a;
            --mb-card: #1e293b;
            --mb-card-hover: #243247;
            --mb-border: #334155;
            --mb-border-active: #10b981;
            --mb-text: #f8fafc;
            --mb-text-muted: #94a3b8;
            --mb-input-bg: #0b1120;
            --mb-input-border: #334155;
            --mb-chip-bg: #0b1120;
            --mb-chip-text: #cbd5e1;
            --mb-qr-bg: #ffffff;
            --mb-qr-border: #475569;
        }

        /* Typography & Components */
        .mb-box {
            background-color: var(--mb-bg);
            border: 1px solid var(--mb-border);
            border-radius: 16px;
            padding: 20px;
            color: var(--mb-text);
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .mb-btn-wa {
            background-color: #25D366;
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            text-decoration: none;
            font-size: 13px;
            transition: opacity 0.2s, transform 0.1s;
        }
        .mb-btn-wa:hover { opacity: 0.92; }
        .mb-btn-wa:active { transform: scale(0.98); }

        .mb-btn-discord {
            background-color: #5865F2;
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            text-decoration: none;
            font-size: 13px;
            transition: opacity 0.2s, transform 0.1s;
        }
        .mb-btn-discord:hover { opacity: 0.92; }
        .mb-btn-discord:active { transform: scale(0.98); }

        /* Accordion */
        .mb-acc-item {
            background-color: var(--mb-bg);
            border: 1px solid var(--mb-border);
            border-radius: 14px;
            margin-bottom: 10px;
            overflow: hidden;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .mb-acc-item.is-active {
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.15);
        }

        .mb-acc-head {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: none;
            border: none;
            cursor: pointer;
            text-align: left;
            color: var(--mb-text);
            font-family: inherit;
            transition: background-color 0.2s;
        }
        .mb-acc-head:hover {
            background-color: var(--mb-card-hover);
        }

        .mb-chevron {
            width: 16px;
            height: 16px;
            color: var(--mb-text-muted);
            transition: transform 0.25s ease;
            transform: rotate(0deg);
            flex-shrink: 0;
        }
        .mb-acc-item.is-active .mb-chevron {
            transform: rotate(180deg) !important;
            color: #10b981;
        }

        .mb-acc-body {
            display: none;
            padding: 18px;
            border-top: 1px solid var(--mb-border);
            background-color: var(--mb-card);
            animation: mbFadeIn 0.2s ease-in-out;
        }
        @keyframes mbFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .mb-acc-item.is-active .mb-acc-body {
            display: block !important;
        }

        .mb-acc-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            align-items: center;
        }
        @media (min-width: 640px) {
            .mb-acc-grid {
                grid-template-columns: 1fr 180px;
            }
        }

        /* Inputs */
        .mb-input {
            width: 100%;
            background-color: var(--mb-input-bg);
            border: 1px solid var(--mb-input-border);
            color: var(--mb-text);
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13px;
            font-family: inherit;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }
        .mb-input:focus {
            border-color: #10b981;
        }

        .mb-btn-submit {
            width: 100%;
            background-color: #10b981;
            color: #ffffff !important;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
            transition: background-color 0.2s, transform 0.1s;
        }
        .mb-btn-submit:hover {
            background-color: #059669;
        }
        .mb-btn-submit:active {
            transform: scale(0.99);
        }

        /* Copy Button */
        .mb-copy-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 8px;
            background-color: var(--mb-chip-bg);
            color: var(--mb-chip-text);
            border: 1px solid var(--mb-border);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }
        .mb-copy-pill:hover {
            border-color: #10b981;
            color: #10b981;
        }
    </style>

    <script>
        // Self-Healing Immediate Definitions for Livewire 3 & DOM Morphing
        window.mbSelectBank = function(idx) {
            if (typeof idx === 'object' && idx !== null) {
                var closest = idx.closest ? idx.closest('.mb-acc-item') : null;
                if (closest && closest.id) {
                    idx = parseInt(closest.id.replace('mb-item-', ''), 10);
                }
            }
            var item = document.getElementById('mb-item-' + idx);
            if (!item) return;
            var isOpen = item.classList.contains('is-active');

            document.querySelectorAll('.mb-acc-item').forEach(function(el) {
                el.classList.remove('is-active');
                var h = el.querySelector('.mb-acc-head');
                if (h) h.setAttribute('aria-expanded', 'false');
            });

            if (!isOpen) {
                item.classList.add('is-active');
                var head = document.getElementById('mb-head-' + idx);
                if (head) head.setAttribute('aria-expanded', 'true');
            }
        };
        function mbSelectBank(idx) {
            window.mbSelectBank(idx);
        }

        window.mbCopy = function(idOrText, btn) {
            var el = (typeof idOrText === 'string') ? document.getElementById(idOrText) : idOrText;
            var text = el ? (el.innerText || el.textContent || '').trim() : (typeof idOrText === 'string' ? idOrText.trim() : '');
            if (!text) return;

            var notify = function() {
                if (!btn) return;
                var s = btn.querySelector ? btn.querySelector('span') : null;
                var targetEl = s || btn;
                var origText = targetEl.getAttribute('data-txt') || targetEl.innerText;
                targetEl.setAttribute('data-txt', origText);
                targetEl.innerText = 'Copied!';
                if (btn.style) {
                    btn.style.borderColor = '#10b981';
                    btn.style.color = '#10b981';
                }
                setTimeout(function() {
                    targetEl.innerText = origText;
                    if (btn.style) {
                        btn.style.borderColor = '';
                        btn.style.color = '';
                    }
                }, 1500);
            };

            var fallback = function(val) {
                try {
                    var ta = document.createElement('textarea');
                    ta.value = val;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.top = '-9999px';
                    ta.style.left = '-9999px';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    ta.setSelectionRange(0, 99999);
                    var success = document.execCommand('copy');
                    document.body.removeChild(ta);
                    if (success) {
                        notify();
                        return;
                    }
                } catch (err) {}
                try { prompt('Copy text: Ctrl+C, Enter', val); } catch (e) {}
            };

            if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(notify).catch(function() {
                    fallback(text);
                });
            } else {
                fallback(text);
            }
        };
        function mbCopy(idOrText, btn) {
            window.mbCopy(idOrText, btn);
        }

        window.mbOpenQr = function(srcOrEl, title) {
            var src = srcOrEl;
            if (srcOrEl && srcOrEl.getAttribute) {
                src = srcOrEl.getAttribute('data-qr-src') || src;
                title = srcOrEl.getAttribute('data-qr-title') || title;
            }
            var modalImg = document.getElementById('mbQrModalImg');
            var modalTitle = document.getElementById('mbQrModalTitle');
            var modal = document.getElementById('mbQrModal');
            if (modalImg && src) modalImg.src = src;
            if (modalTitle && title) modalTitle.innerText = title;
            if (modal) modal.style.display = 'flex';
        };
        function mbOpenQr(srcOrEl, title) {
            window.mbOpenQr(srcOrEl, title);
        }

        window.mbCloseQr = function() {
            var modal = document.getElementById('mbQrModal');
            if (modal) modal.style.display = 'none';
        };
        function mbCloseQr() {
            window.mbCloseQr();
        }
    </script>

    <!-- 1. Header Card: Invoice #, Status & Amount -->
    <div class="mb-box">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid var(--mb-border);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <img src="https://raw.githubusercontent.com/Hasnain1385/Paymenter_Payment/main/logo.png" alt="PakBank" style="width: 44px; height: 44px; border-radius: 12px; object-fit: contain; box-shadow: 0 2px 5px rgba(0,0,0,0.08); flex-shrink: 0;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <span style="font-size: 12px; font-weight: 700; color: var(--mb-text-muted); text-transform: uppercase;">
                            Invoice #{{ $invoiceId }}
                        </span>
                        @if($isVerified)
                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: rgba(16, 185, 129, 0.15); color: #10b981;">Paid</span>
                        @elseif($isPendingVerif)
                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">Under Verification</span>
                        @else
                            <span style="font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; background: var(--mb-chip-bg); color: var(--mb-text-muted);">Pending Payment</span>
                        @endif
                    </div>
                    <div style="font-size: 18px; font-weight: 800; color: var(--mb-text);">
                        {{ $gatewayTitle ?? 'Bank Transfer & QR Pay' }}
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 11px; font-weight: 600; color: var(--mb-text-muted); text-transform: uppercase;">Amount Due</div>
                <div style="font-size: 26px; font-weight: 900; color: var(--mb-text); letter-spacing: -0.5px;">
                    <span style="font-size: 14px; font-weight: 700; color: var(--mb-text-muted); margin-right: 2px;">{{ $invoiceCurrency }}</span>{{ $invoiceTotal }}
                </div>
            </div>
        </div>

        <!-- 2. Two Contact Buttons Below the Amount -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-top: 14px;">
            @if(!empty($whatsappPrePayUrl) || !empty($whatsappPhone))
                <a href="{{ $whatsappPrePayUrl ?? ('https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsappPhone)) }}"
                   target="_blank" rel="noopener noreferrer" class="mb-btn-wa">
                    <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.153.57 4.175 1.564 5.921l-1.636 5.981 6.136-1.609c1.706.929 3.659 1.457 5.736 1.457 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z"/></svg>
                    <span>Chat on WhatsApp</span>
                </a>
            @endif

            @if(!empty($discordInvite))
                <a href="{{ $discordInvite }}"
                   target="_blank" rel="noopener noreferrer" class="mb-btn-discord">
                    <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.929 1.793 8.18 1.793 12.061 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.893.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.028zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>
                    <span>Join Discord</span>
                </a>
            @endif
        </div>

        @if(!empty($customNotice))
            <div style="margin-top: 14px; padding: 10px 14px; border-radius: 10px; background-color: var(--mb-card); border: 1px solid var(--mb-border); font-size: 12px; color: var(--mb-text-muted);">
                {{ $customNotice }}
            </div>
        @endif
    </div>

    <!-- 3. Collapsible Bank Accounts Accordion (Toggle to Expand / Collapse) -->
    <div style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; padding: 0 4px;">
            <div style="font-size: 11px; font-weight: 700; color: var(--mb-text-muted); text-transform: uppercase;">
                Select Payment Method
            </div>
            <div style="font-size: 11px; color: var(--mb-text-muted);">
                Click account to collapse / expand
            </div>
        </div>

        @if(empty($accounts) || count($accounts) === 0)
            <div class="mb-box" style="text-align: center; color: var(--mb-text-muted); padding: 30px;">
                <p style="font-size: 14px; font-weight: 600; margin: 0;">No active bank accounts found.</p>
                <p style="font-size: 12px; margin-top: 4px;">Please contact support on WhatsApp or Discord.</p>
            </div>
        @else
            <div id="mbAccordion">
                @foreach($accounts as $acc)
                    @php
                        $isActive = $loop->first;
                    @endphp
                    <div id="mb-item-{{ $loop->index }}" class="mb-acc-item {{ $isActive ? 'is-active' : '' }}">
                        <!-- Accordion Header (Click to Toggle Collapse/Expand) -->
                        <button type="button" class="mb-acc-head" id="mb-head-{{ $loop->index }}"
                            onclick="(window.mbSelectBank || function(idx) {
                                var item = document.getElementById('mb-item-' + idx);
                                if (!item) return;
                                var isOpen = item.classList.contains('is-active');
                                document.querySelectorAll('.mb-acc-item').forEach(function(el) {
                                    el.classList.remove('is-active');
                                    var h = el.querySelector('.mb-acc-head');
                                    if (h) h.setAttribute('aria-expanded', 'false');
                                });
                                if (!isOpen) {
                                    item.classList.add('is-active');
                                    var head = document.getElementById('mb-head-' + idx);
                                    if (head) head.setAttribute('aria-expanded', 'true');
                                }
                            }).call(this, {{ $loop->index }})"
                            aria-expanded="{{ $isActive ? 'true' : 'false' }}"
                            title="Click to collapse or expand">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background-color: var(--mb-chip-bg); border: 1px solid var(--mb-border); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; color: #10b981; flex-shrink: 0;">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 700; color: var(--mb-text);">
                                        {{ $acc['name'] }}
                                    </div>
                                    @if(!empty($acc['badge']))
                                        <div style="font-size: 11px; font-weight: 600; color: #10b981;">
                                            {{ $acc['badge'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <svg class="mb-chevron" id="mb-chevron-{{ $loop->index }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Accordion Content (Left Details, Right QR) -->
                        <div id="mb-body-{{ $loop->index }}" class="mb-acc-body">
                            <div class="mb-acc-grid">

                                <!-- LEFT COLUMN: Details -->
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <!-- Account Title -->
                                    <div>
                                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--mb-text-muted); margin-bottom: 2px;">Account Title</div>
                                        <div style="font-size: 14px; font-weight: 700; color: var(--mb-text); user-select: all;">{{ $acc['account_title'] }}</div>
                                    </div>

                                    <!-- Account Number / IBAN with 1-Click Copy -->
                                    <div>
                                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--mb-text-muted); margin-bottom: 2px;">Account Number / IBAN / Raast ID</div>
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span id="mb-acc-num-{{ $loop->index }}"
                                                  onclick="(window.mbCopy = window.mbCopy || function(id, btn) { var el = document.getElementById(id); var t = el ? (el.innerText || el.textContent || '').trim() : id; if (!t) return; var cb = function() { if (!btn) return; var s = btn.querySelector('span') || btn; var orig = s.getAttribute('data-txt') || s.innerText; s.setAttribute('data-txt', orig); s.innerText = 'Copied!'; btn.style.borderColor = '#10b981'; btn.style.color = '#10b981'; setTimeout(function() { s.innerText = orig; btn.style.borderColor = ''; btn.style.color = ''; }, 1500); }; if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) { navigator.clipboard.writeText(t).then(cb).catch(function() { fb(t); }); } else { fb(t); } function fb(val) { try { var ta = document.createElement('textarea'); ta.value = val; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); cb(); } catch(e) {} } })('mb-acc-num-{{ $loop->index }}', document.getElementById('mb-copy-acc-{{ $loop->index }}'))"
                                                  title="Click to copy account number"
                                                  style="font-family: monospace; font-size: 14px; font-weight: 800; color: #10b981; background: var(--mb-bg); padding: 6px 10px; border-radius: 8px; border: 1px solid var(--mb-border); user-select: all; cursor: pointer;">
                                                {{ $acc['account_number'] }}
                                            </span>
                                            <button type="button" id="mb-copy-acc-{{ $loop->index }}" class="mb-copy-pill" title="Copy account number"
                                                onclick="(window.mbCopy = window.mbCopy || function(id, btn) { var el = document.getElementById(id); var t = el ? (el.innerText || el.textContent || '').trim() : id; if (!t) return; var cb = function() { if (!btn) return; var s = btn.querySelector('span') || btn; var orig = s.getAttribute('data-txt') || s.innerText; s.setAttribute('data-txt', orig); s.innerText = 'Copied!'; btn.style.borderColor = '#10b981'; btn.style.color = '#10b981'; setTimeout(function() { s.innerText = orig; btn.style.borderColor = ''; btn.style.color = ''; }, 1500); }; if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) { navigator.clipboard.writeText(t).then(cb).catch(function() { fb(t); }); } else { fb(t); } function fb(val) { try { var ta = document.createElement('textarea'); ta.value = val; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); cb(); } catch(e) {} } })('mb-acc-num-{{ $loop->index }}', this)">
                                                <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Copy</span>
                                            </button>
                                        </div>
                                    </div>

                                    @if(!empty($acc['routing_code']))
                                        <div>
                                            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--mb-text-muted); margin-bottom: 2px;">Bank / Branch</div>
                                            <div style="font-size: 12px; font-weight: 500; color: var(--mb-text);">{{ $acc['routing_code'] }}</div>
                                        </div>
                                    @endif

                                    <!-- Transfer Reference -->
                                    <div>
                                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--mb-text-muted); margin-bottom: 2px;">Transfer Remarks / Reference</div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span id="mb-ref-{{ $loop->index }}"
                                                  onclick="(window.mbCopy = window.mbCopy || function(id, btn) { var el = document.getElementById(id); var t = el ? (el.innerText || el.textContent || '').trim() : id; if (!t) return; var cb = function() { if (!btn) return; var s = btn.querySelector('span') || btn; var orig = s.getAttribute('data-txt') || s.innerText; s.setAttribute('data-txt', orig); s.innerText = 'Copied!'; btn.style.borderColor = '#10b981'; btn.style.color = '#10b981'; setTimeout(function() { s.innerText = orig; btn.style.borderColor = ''; btn.style.color = ''; }, 1500); }; if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) { navigator.clipboard.writeText(t).then(cb).catch(function() { fb(t); }); } else { fb(t); } function fb(val) { try { var ta = document.createElement('textarea'); ta.value = val; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); cb(); } catch(e) {} } })('mb-ref-{{ $loop->index }}', document.getElementById('mb-copy-ref-{{ $loop->index }}'))"
                                                  title="Click to copy transfer remarks"
                                                  style="font-family: monospace; font-size: 12px; font-weight: 700; color: var(--mb-text); background: var(--mb-bg); padding: 4px 8px; border-radius: 6px; border: 1px solid var(--mb-border); user-select: all; cursor: pointer;">INV-{{ $invoiceId }}</span>
                                            <button type="button" id="mb-copy-ref-{{ $loop->index }}" class="mb-copy-pill" title="Copy transfer remarks"
                                                onclick="(window.mbCopy = window.mbCopy || function(id, btn) { var el = document.getElementById(id); var t = el ? (el.innerText || el.textContent || '').trim() : id; if (!t) return; var cb = function() { if (!btn) return; var s = btn.querySelector('span') || btn; var orig = s.getAttribute('data-txt') || s.innerText; s.setAttribute('data-txt', orig); s.innerText = 'Copied!'; btn.style.borderColor = '#10b981'; btn.style.color = '#10b981'; setTimeout(function() { s.innerText = orig; btn.style.borderColor = ''; btn.style.color = ''; }, 1500); }; if (navigator.clipboard && window.isSecureContext && navigator.clipboard.writeText) { navigator.clipboard.writeText(t).then(cb).catch(function() { fb(t); }); } else { fb(t); } function fb(val) { try { var ta = document.createElement('textarea'); ta.value = val; ta.setAttribute('readonly', ''); ta.style.position = 'fixed'; ta.style.opacity = '0'; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); cb(); } catch(e) {} } })('mb-ref-{{ $loop->index }}', this)">
                                                <svg style="width: 12px; height: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                <span>Copy</span>
                                            </button>
                                        </div>
                                    </div>

                                    @if(!empty($acc['instructions']))
                                        <div style="font-size: 11px; color: var(--mb-text-muted); padding: 6px 10px; border-radius: 8px; background: var(--mb-bg); border: 1px solid var(--mb-border);">
                                            {{ $acc['instructions'] }}
                                        </div>
                                    @endif
                                </div>

                                <!-- RIGHT COLUMN: Scanned QR Frame -->
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                                    @if(!empty($acc['qr_src']))
                                        <div class="mb-qr-box"
                                             data-qr-src="{{ $acc['qr_src'] }}"
                                             data-qr-title="{{ htmlspecialchars($acc['name'], ENT_QUOTES, 'UTF-8') }}"
                                             onclick="(window.mbOpenQr = window.mbOpenQr || function(btn) { var src = btn.getAttribute('data-qr-src'); var title = btn.getAttribute('data-qr-title'); var mI = document.getElementById('mbQrModalImg'); var mT = document.getElementById('mbQrModalTitle'); var m = document.getElementById('mbQrModal'); if(mI) mI.src = src; if(mT) mT.innerText = title; if(m) m.style.display = 'flex'; })(this)"
                                             style="background-color: var(--mb-qr-bg); border: 1px solid var(--mb-qr-border); border-radius: 12px; padding: 10px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                            <img src="{{ $acc['qr_src'] }}" alt="QR" style="width: 130px; height: 130px; object-fit: contain; display: block; border-radius: 6px;">
                                        </div>
                                        <div style="font-size: 11px; font-weight: 600; color: var(--mb-text-muted); margin-top: 6px; cursor: pointer;"
                                             data-qr-src="{{ $acc['qr_src'] }}"
                                             data-qr-title="{{ htmlspecialchars($acc['name'], ENT_QUOTES, 'UTF-8') }}"
                                             onclick="(window.mbOpenQr = window.mbOpenQr || function(btn) { var src = btn.getAttribute('data-qr-src'); var title = btn.getAttribute('data-qr-title'); var mI = document.getElementById('mbQrModalImg'); var mT = document.getElementById('mbQrModalTitle'); var m = document.getElementById('mbQrModal'); if(mI) mI.src = src; if(mT) mT.innerText = title; if(m) m.style.display = 'flex'; })(this)">
                                            Scan to Pay
                                        </div>
                                    @else
                                        <div style="width: 130px; height: 130px; border: 1px dashed var(--mb-border); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; font-size: 11px; color: var(--mb-text-muted);">
                                            <span>Manual Transfer</span>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 4. Payment Confirmation / Proof Form Card -->
    <div class="mb-box">
        @if($isVerified)
            <!-- Verified State -->
            <div style="text-align: center; padding: 16px 0;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                    <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div style="font-size: 16px; font-weight: 800; color: var(--mb-text);">Payment Verified</div>
                <p style="font-size: 12px; color: var(--mb-text-muted); margin: 4px 0 14px;">Your payment has been confirmed and this invoice is paid.</p>
                <a href="{{ url('/invoices/' . $invoiceId) }}" class="mb-btn-submit" style="display: inline-block; width: auto; padding: 8px 18px; text-decoration: none; font-size: 13px;">View Invoice</a>
            </div>
        @elseif($isPendingVerif)
            <!-- Pending Verification State with Auto-Polling -->
            <div style="padding: 10px 0;">
                <div style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                    <div style="color: #f59e0b; margin-top: 2px;">
                        <svg style="width: 20px; height: 20px; animation: spin 1s linear infinite;" fill="none" viewBox="0 0 24 24"><circle style="opacity: 0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path style="opacity: 0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                    <div style="font-size: 12px; color: var(--mb-text); flex: 1;">
                        <div style="font-weight: 700; font-size: 13px; color: #f59e0b; margin-bottom: 2px;">Verification in Progress</div>
                        <div>Your payment (TRX: <strong style="font-family: monospace;">{{ $transaction->transaction_id }}</strong>) has been submitted. Our team will verify and activate your order shortly.</div>
                        <div style="font-size: 11px; color: var(--mb-text-muted); margin-top: 6px;">This page updates automatically once approved.</div>
                    </div>
                </div>

                @if(!empty($whatsappPostPayUrl))
                    <div style="text-align: center; margin-top: 14px;">
                        <a href="{{ $whatsappPostPayUrl }}" target="_blank" rel="noopener noreferrer" class="mb-btn-wa" style="font-size: 12px; padding: 8px 14px;">
                            <span>Send Screenshot on WhatsApp</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <!-- Payment Proof Submission Form -->
            <form action="{{ route('multibankqr.submit-proof', $invoiceId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 14px; font-weight: 700; color: var(--mb-text);">Confirm Payment</div>
                    <div style="font-size: 12px; color: var(--mb-text-muted); margin-top: 2px;">Enter your transaction reference after making the transfer.</div>
                </div>

                @if($errors->any())
                    <div style="padding: 10px 14px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 12px; margin-bottom: 12px;">
                        @foreach($errors->all() as $err)
                            <div>• {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <!-- Transaction ID -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--mb-text-muted); text-transform: uppercase; margin-bottom: 4px;">
                            Transaction ID / Reference (TRX) *
                        </label>
                        <input type="text" name="transaction_id" required placeholder="e.g. 1928374652" class="mb-input" style="font-family: monospace;">
                    </div>

                    <!-- Sender Name / Phone -->
                    <div>
                        <label style="display: block; font-size: 11px; font-weight: 700; color: var(--mb-text-muted); text-transform: uppercase; margin-bottom: 4px;">
                            Sender Name or Mobile Number *
                        </label>
                        <input type="text" name="sender_name" required placeholder="e.g. Ali or 03001234567" class="mb-input">
                    </div>
                </div>

                <!-- Proof Screenshot Upload -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 11px; font-weight: 700; color: var(--mb-text-muted); text-transform: uppercase; margin-bottom: 4px;">
                        Payment Screenshot / Receipt {{ $requireProofImage ? '*' : '(Optional)' }}
                    </label>
                    <input type="file" name="proof_image" {{ $requireProofImage ? 'required' : '' }} accept="image/jpeg,image/png,image/webp,application/pdf" class="mb-input" style="padding: 6px;">
                </div>

                <!-- Submit Button -->
                <button type="submit" class="mb-btn-submit">
                    Submit Payment Details
                </button>
            </form>
        @endif
    </div>

</div>

<!-- Lightbox Modal for Enlargeable QR Image -->
<div id="mbQrModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.85); align-items: center; justify-content: center; padding: 16px;" onclick="(window.mbCloseQr || function(){ var m = document.getElementById('mbQrModal'); if(m) m.style.display='none'; })()">
    <div style="background: #ffffff; border-radius: 16px; max-width: 340px; width: 100%; padding: 20px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; margin-bottom: 14px;">
            <div id="mbQrModalTitle" style="font-size: 14px; font-weight: 700; color: #0f172a;">Bank QR Code</div>
            <button type="button" onclick="(window.mbCloseQr || function(){ var m = document.getElementById('mbQrModal'); if(m) m.style.display='none'; })()" style="background: none; border: none; font-size: 20px; line-height: 1; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <div style="background: #ffffff; padding: 10px; border-radius: 10px; display: inline-block;">
            <img id="mbQrModalImg" src="" alt="QR" style="width: 220px; height: 220px; object-fit: contain; display: block;">
        </div>
        <p style="font-size: 11px; color: #64748b; margin: 10px 0 0;">Scan with your mobile banking app.</p>
    </div>
</div>

<script>
    // 1-Click Copy with Feedback & Secure Fallback
    function mbCopy(id, btn) {
        if (window.mbCopy) {
            window.mbCopy(id, btn);
        }
    }

    function mbOpenQr(srcOrEl, title) {
        if (window.mbOpenQr) {
            window.mbOpenQr(srcOrEl, title);
        }
    }

    function mbCloseQr() {
        if (window.mbCloseQr) {
            window.mbCloseQr();
        }
    }

    // Real-Time Background Status Polling
    @if($isPendingVerif)
    const pollInterval = setInterval(async () => {
        try {
            const res = await fetch('{{ route("multibankqr.status", $invoiceId) }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.status === 'verified' || data.invoice_status === 'paid') {
                clearInterval(pollInterval);
                window.location.reload();
            }
        } catch (e) {
            // Network fallback
        }
    }, 5000);
    @endif
</script>
