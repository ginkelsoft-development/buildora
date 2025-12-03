@extends('buildora::layouts.buildora')

@section('content')
    <div class="w-full">
        {{-- Back Button --}}
        <a href="{{ route('buildora.profile.edit') }}"
           class="inline-flex items-center text-sm mb-6 transition-colors hover:opacity-80"
           style="color: var(--text-muted);">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            {{ __buildora('Back to Profile') }}
        </a>

        <div class="rounded-xl shadow-sm overflow-hidden" style="background: var(--bg-dropdown); border: 1px solid var(--border-color);">
            {{-- Header --}}
            <div class="p-6 flex items-center gap-4" style="border-bottom: 1px solid var(--border-color);">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: rgba(16, 185, 129, 0.1);">
                    <i class="fa-solid fa-shield-check text-xl text-emerald-500"></i>
                </div>
                <div>
                    <h1 class="text-lg font-semibold" style="color: var(--text-primary);">
                        {{ __buildora('Two-Factor Authentication Enabled') }}
                    </h1>
                    <p class="text-sm" style="color: var(--text-muted);">
                        {{ __buildora('Save these recovery codes in a safe place.') }}
                    </p>
                </div>
            </div>

            <div class="p-6">
                {{-- Warning --}}
                <div class="p-4 rounded-lg flex items-start gap-3 mb-6" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2);">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">
                            {{ __buildora('Important') }}
                        </p>
                        <p class="text-sm mt-1 text-amber-600/80 dark:text-amber-400/80">
                            {{ __buildora('These codes can be used to access your account if you lose your authenticator device. Each code can only be used once.') }}
                        </p>
                    </div>
                </div>

                {{-- Recovery Codes --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-6" id="recovery-codes-grid">
                    @foreach($recoveryCodes as $code)
                        <div class="px-3 py-3 rounded-lg text-center font-mono text-sm select-all"
                             style="background: var(--bg-input); color: var(--text-primary); border: 1px solid var(--border-color);">
                            <span class="tracking-wide font-semibold">{{ $code }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex flex-wrap gap-3">
                    <button type="button"
                            onclick="copyRecoveryCodes()"
                            id="copy-btn"
                            class="py-2.5 px-4 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
                            style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                        <i class="fa-solid fa-copy"></i>
                        <span id="copy-text">{{ __buildora('Copy to Clipboard') }}</span>
                    </button>
                    <button type="button"
                            onclick="downloadPDF()"
                            class="py-2.5 px-4 rounded-lg text-sm font-medium transition-all flex items-center gap-2"
                            style="background: var(--bg-input); border: 1px solid var(--border-color); color: var(--text-primary);">
                        <i class="fa-solid fa-file-pdf"></i>
                        {{ __buildora('Download PDF') }}
                    </button>
                    <a href="{{ route('buildora.profile.edit') }}"
                       class="py-2.5 px-5 rounded-lg text-sm font-medium text-white transition-all flex items-center gap-2 btn-primary ml-auto">
                        <i class="fa-solid fa-check"></i>
                        {{ __buildora('Done') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const recoveryCodes = @json($recoveryCodes);
        const appName = '{{ config('app.name', 'Buildora') }}';
        const userName = '{{ auth()->user()->name }}';
        const userEmail = '{{ auth()->user()->email }}';

        function copyRecoveryCodes() {
            const text = recoveryCodes.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.getElementById('copy-btn');
                const textEl = document.getElementById('copy-text');
                const originalText = textEl.innerText;

                textEl.innerText = '{{ __buildora('Copied!') }}';
                btn.style.background = 'rgba(16, 185, 129, 0.1)';
                btn.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                btn.style.color = '#10b981';

                setTimeout(() => {
                    textEl.innerText = originalText;
                    btn.style.background = '';
                    btn.style.borderColor = '';
                    btn.style.color = '';
                }, 2000);
            });
        }

        function downloadPDF() {
            // Create a new window for the PDF content
            const printWindow = window.open('', '_blank');

            const html = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Recovery Codes - ${appName}</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            padding: 40px;
                            color: #1a1a1a;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 30px;
                            padding-bottom: 20px;
                            border-bottom: 2px solid #e5e7eb;
                        }
                        .logo {
                            font-size: 24px;
                            font-weight: bold;
                            color: #667eea;
                            margin-bottom: 8px;
                        }
                        .title {
                            font-size: 20px;
                            font-weight: 600;
                            color: #111827;
                            margin-bottom: 8px;
                        }
                        .subtitle {
                            font-size: 14px;
                            color: #6b7280;
                        }
                        .user-info {
                            background: #f9fafb;
                            border: 1px solid #e5e7eb;
                            border-radius: 8px;
                            padding: 16px;
                            margin-bottom: 24px;
                        }
                        .user-info p {
                            font-size: 13px;
                            color: #374151;
                            margin-bottom: 4px;
                        }
                        .user-info p:last-child { margin-bottom: 0; }
                        .user-info strong { color: #111827; }
                        .warning {
                            background: #fef3c7;
                            border: 1px solid #f59e0b;
                            border-radius: 8px;
                            padding: 16px;
                            margin-bottom: 24px;
                        }
                        .warning-title {
                            font-weight: 600;
                            color: #92400e;
                            margin-bottom: 6px;
                            font-size: 14px;
                        }
                        .warning-text {
                            font-size: 13px;
                            color: #a16207;
                            line-height: 1.5;
                        }
                        .codes-title {
                            font-size: 14px;
                            font-weight: 600;
                            color: #374151;
                            margin-bottom: 12px;
                        }
                        .codes-grid {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 10px;
                            margin-bottom: 30px;
                        }
                        .code {
                            background: #f3f4f6;
                            border: 1px solid #d1d5db;
                            border-radius: 6px;
                            padding: 12px 8px;
                            text-align: center;
                            font-family: 'SF Mono', Monaco, 'Courier New', monospace;
                            font-size: 12px;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                            color: #1f2937;
                        }
                        .footer {
                            text-align: center;
                            padding-top: 20px;
                            border-top: 1px solid #e5e7eb;
                            font-size: 11px;
                            color: #9ca3af;
                        }
                        .footer p { margin-bottom: 4px; }
                        @media print {
                            body { padding: 20px; }
                            .codes-grid { page-break-inside: avoid; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="logo">${appName}</div>
                        <div class="title">Two-Factor Authentication Recovery Codes</div>
                        <div class="subtitle">Keep these codes safe and secure</div>
                    </div>

                    <div class="user-info">
                        <p><strong>Account:</strong> ${userName}</p>
                        <p><strong>Email:</strong> ${userEmail}</p>
                        <p><strong>Generated:</strong> ${new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                    </div>

                    <div class="warning">
                        <div class="warning-title">Important Security Notice</div>
                        <div class="warning-text">
                            These recovery codes can be used to access your account if you lose your authenticator device.
                            Each code can only be used once. Store this document in a secure location and do not share it with anyone.
                        </div>
                    </div>

                    <div class="codes-title">Your Recovery Codes</div>
                    <div class="codes-grid">
                        ${recoveryCodes.map(code => `<div class="code">${code}</div>`).join('')}
                    </div>

                    <div class="footer">
                        <p>This document was generated by ${appName}</p>
                        <p>For security purposes, regenerate your codes if you believe they have been compromised.</p>
                    </div>
                </body>
                </html>
            `;

            printWindow.document.write(html);
            printWindow.document.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                printWindow.print();
            };
        }
    </script>
@endsection
