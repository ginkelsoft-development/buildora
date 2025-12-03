<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install Buildora</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #fff;
            min-height: 100vh;
        }

        .install-container {
            min-height: 100vh;
            display: flex;
        }

        /* Left Panel - Branding */
        .brand-panel {
            width: 40%;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f0f23 100%);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            animation: pulse 15s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .brand-content {
            position: relative;
            z-index: 1;
        }

        .brand-logo {
            margin-bottom: 40px;
        }

        .brand-title {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .brand-title span {
            color: #667eea;
        }

        .brand-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.6);
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .brand-features {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
        }

        .brand-feature-icon {
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-feature-icon i {
            font-size: 14px;
        }

        .brand-version {
            position: absolute;
            bottom: 30px;
            left: 60px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 14px;
        }

        /* Right Panel - Install Form */
        .install-panel {
            flex: 1;
            background: #0f0f1a;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .install-content {
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }

        .install-header {
            margin-bottom: 40px;
        }

        .install-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .install-header p {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Steps indicator */
        .steps-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .steps-indicator::before {
            content: '';
            position: absolute;
            top: 16px;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
        }

        .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background: #667eea;
        }

        .step-dot.completed {
            background: #10b981;
        }

        /* Requirements section */
        .requirements-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 16px;
        }

        .requirements-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .requirement-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .requirement-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .requirement-icon.passed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .requirement-icon.failed {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .requirement-value {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }

        /* Form section */
        .form-section {
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 6px;
        }

        /* Install button */
        .install-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .install-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(102, 126, 234, 0.5);
        }

        .install-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .install-button .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Progress section */
        .progress-section {
            display: none;
        }

        .progress-section.active {
            display: block;
        }

        .progress-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 30px;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .progress-item.pending {
            opacity: 0.5;
        }

        .progress-item.running {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .progress-item.completed {
            border-color: #10b981;
        }

        .progress-item.failed {
            border-color: #ef4444;
        }

        .progress-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .progress-item.pending .progress-icon {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
        }

        .progress-item.running .progress-icon {
            background: #667eea;
        }

        .progress-item.running .progress-icon i {
            animation: spin 1s linear infinite;
        }

        .progress-item.completed .progress-icon {
            background: #10b981;
        }

        .progress-item.failed .progress-icon {
            background: #ef4444;
        }

        .progress-text {
            flex: 1;
        }

        .progress-title {
            font-weight: 500;
            margin-bottom: 2px;
        }

        .progress-message {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Success section */
        .success-section {
            display: none;
            text-align: center;
        }

        .success-section.active {
            display: block;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .success-icon i {
            font-size: 36px;
            color: #10b981;
        }

        .success-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .success-message {
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }

        .success-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .success-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px -10px rgba(102, 126, 234, 0.5);
        }

        /* Error message */
        .error-message {
            display: none;
            padding: 16px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 10px;
            color: #ef4444;
            margin-bottom: 20px;
        }

        .error-message.active {
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .brand-panel {
                display: none;
            }

            .install-panel {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="install-container">
        <!-- Left Panel - Branding -->
        <div class="brand-panel">
            <div class="brand-content">
                <div class="brand-logo">
                    <img src="{{ route('buildora.asset', ['file' => 'buildora.png']) }}" alt="Buildora" style="height: 50px; filter: brightness(0) invert(1);">
                </div>

                <h1 class="brand-title">
                    Build something<br>
                    <span>amazing today</span>
                </h1>

                <p class="brand-description">
                    Powerful admin panels, resources, and datatables — fully based on your Eloquent models.
                </p>

                <div class="brand-features">
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <i class="fas fa-bolt" style="color: #fbbf24;"></i>
                        </div>
                        <span>Lightning fast development</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <i class="fas fa-shield-halved" style="color: #34d399;"></i>
                        </div>
                        <span>Built-in permissions</span>
                    </div>
                    <div class="brand-feature">
                        <div class="brand-feature-icon">
                            <i class="fas fa-palette" style="color: #f472b6;"></i>
                        </div>
                        <span>Fully customizable</span>
                    </div>
                </div>
            </div>

            <div class="brand-version">
                Buildora v{{ $version }}
            </div>
        </div>

        <!-- Right Panel - Install -->
        <div class="install-panel">
            <div class="install-content">
                <!-- Header -->
                <div class="install-header">
                    <h1>Installation Wizard</h1>
                    <p>Let's get Buildora set up for your project</p>
                </div>

                <!-- Steps indicator -->
                <div class="steps-indicator">
                    <div class="step-dot active" data-step="1">1</div>
                    <div class="step-dot" data-step="2">2</div>
                    <div class="step-dot" data-step="3">3</div>
                </div>

                <!-- Step 1: Requirements -->
                <div id="step-requirements" class="step-content">
                    <div class="requirements-section">
                        <h3 class="section-title">System Requirements</h3>
                        <div class="requirements-list">
                            @foreach($requirements as $key => $req)
                                <div class="requirement-item" data-requirement="{{ $key }}">
                                    <div class="requirement-label">
                                        <div class="requirement-icon {{ $req['passed'] ? 'passed' : 'failed' }}">
                                            <i class="fas {{ $req['passed'] ? 'fa-check' : 'fa-times' }}"></i>
                                        </div>
                                        <span>{{ $req['label'] }}</span>
                                    </div>
                                    <span class="requirement-value">{{ $req['current'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $allPassed = collect($requirements)->every(fn($r) => $r['passed']);
                    @endphp

                    @if(!$allPassed)
                        <div class="error-message active">
                            <i class="fas fa-exclamation-triangle"></i>
                            Some requirements are not met. Please fix them before continuing.
                        </div>
                    @endif

                    <button type="button" class="install-button" id="btn-next-step" {{ !$allPassed ? 'disabled' : '' }}>
                        Continue
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

                <!-- Step 2: Admin User Form -->
                <div id="step-form" class="step-content" style="display: none;">
                    <form id="install-form">
                        <div class="form-section">
                            <h3 class="section-title">Create Admin Account</h3>

                            <div class="form-group">
                                <label class="form-label" for="admin_name">Name</label>
                                <input type="text" id="admin_name" name="admin_name" class="form-input" placeholder="John Doe" required>
                                <div class="form-error" id="error-admin_name"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="admin_email">Email</label>
                                <input type="email" id="admin_email" name="admin_email" class="form-input" placeholder="admin@example.com" required>
                                <div class="form-error" id="error-admin_email"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="admin_password">Password</label>
                                <input type="password" id="admin_password" name="admin_password" class="form-input" placeholder="••••••••" required minlength="8">
                                <div class="form-error" id="error-admin_password"></div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="admin_password_confirmation">Confirm Password</label>
                                <input type="password" id="admin_password_confirmation" name="admin_password_confirmation" class="form-input" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="error-message" id="form-error"></div>

                        <button type="submit" class="install-button" id="btn-install">
                            <span class="btn-text">Install Buildora</span>
                            <span class="spinner" style="display: none;"></span>
                        </button>
                    </form>
                </div>

                <!-- Step 3: Progress -->
                <div id="step-progress" class="step-content" style="display: none;">
                    <div class="progress-section active">
                        <h3 class="section-title">Installing Buildora</h3>

                        <div class="progress-list" id="progress-list">
                            <div class="progress-item pending" data-step="config">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Publishing configuration</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="migrations">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Running migrations</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="user-model">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Configuring User model</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="directory">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Setting up Buildora directory</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="resources">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Generating resources</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="permissions">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Generating permissions</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="admin">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Creating admin user</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                            <div class="progress-item pending" data-step="finalize">
                                <div class="progress-icon"><i class="fas fa-circle"></i></div>
                                <div class="progress-text">
                                    <div class="progress-title">Finalizing installation</div>
                                    <div class="progress-message">Waiting...</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="success-section" id="success-section">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <h2 class="success-title">Installation Complete!</h2>
                        <p class="success-message">Buildora has been successfully installed and configured.</p>
                        <a href="{{ route('buildora.login') }}" class="success-button">
                            Go to Login
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const steps = {
                requirements: document.getElementById('step-requirements'),
                form: document.getElementById('step-form'),
                progress: document.getElementById('step-progress')
            };

            const stepDots = document.querySelectorAll('.step-dot');
            let currentStep = 1;

            function setStep(step) {
                currentStep = step;

                // Update step dots
                stepDots.forEach((dot, index) => {
                    dot.classList.remove('active', 'completed');
                    if (index + 1 < step) {
                        dot.classList.add('completed');
                        dot.innerHTML = '<i class="fas fa-check"></i>';
                    } else if (index + 1 === step) {
                        dot.classList.add('active');
                    }
                });

                // Show/hide step content
                Object.values(steps).forEach(el => el.style.display = 'none');

                if (step === 1) steps.requirements.style.display = 'block';
                if (step === 2) steps.form.style.display = 'block';
                if (step === 3) steps.progress.style.display = 'block';
            }

            // Next step button
            document.getElementById('btn-next-step').addEventListener('click', function() {
                setStep(2);
            });

            // Install form submit
            document.getElementById('install-form').addEventListener('submit', async function(e) {
                e.preventDefault();

                const btn = document.getElementById('btn-install');
                const btnText = btn.querySelector('.btn-text');
                const spinner = btn.querySelector('.spinner');

                // Clear errors
                document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
                document.getElementById('form-error').classList.remove('active');

                // Validate passwords match
                const password = document.getElementById('admin_password').value;
                const confirmation = document.getElementById('admin_password_confirmation').value;

                if (password !== confirmation) {
                    document.getElementById('error-admin_password').textContent = 'Passwords do not match';
                    return;
                }

                // Show loading
                btn.disabled = true;
                btnText.textContent = 'Installing...';
                spinner.style.display = 'block';

                // Move to progress step
                setStep(3);

                const progressItems = document.querySelectorAll('.progress-item');
                const formData = new FormData(this);

                function updateProgress(index, status, message) {
                    const item = progressItems[index];
                    if (!item) return;

                    item.classList.remove('pending', 'running', 'completed', 'failed');
                    item.classList.add(status);

                    const icon = item.querySelector('.progress-icon i');
                    const msgEl = item.querySelector('.progress-message');

                    if (status === 'running') {
                        icon.className = 'fas fa-spinner fa-spin';
                        msgEl.textContent = 'Processing...';
                    } else if (status === 'completed') {
                        icon.className = 'fas fa-check';
                        msgEl.textContent = message || 'Done';
                    } else if (status === 'failed') {
                        icon.className = 'fas fa-times';
                        msgEl.textContent = message || 'Failed';
                    }
                }

                // Start first step
                updateProgress(0, 'running');

                try {
                    const response = await fetch('{{ route("buildora.install.process") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            admin_name: formData.get('admin_name'),
                            admin_email: formData.get('admin_email'),
                            admin_password: formData.get('admin_password'),
                            admin_password_confirmation: formData.get('admin_password_confirmation'),
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Animate through all steps
                        for (let i = 0; i < progressItems.length; i++) {
                            updateProgress(i, 'completed', data.steps[i] ? data.steps[i].message : 'Done');
                            await new Promise(resolve => setTimeout(resolve, 200));
                        }

                        // Show success section
                        setTimeout(() => {
                            document.querySelector('.progress-section').style.display = 'none';
                            document.getElementById('success-section').style.display = 'block';
                            document.getElementById('success-section').classList.add('active');
                        }, 500);
                    } else {
                        // Mark completed steps and find failed step
                        if (data.steps && data.steps.length > 0) {
                            for (let i = 0; i < data.steps.length; i++) {
                                if (data.steps[i].success) {
                                    updateProgress(i, 'completed', data.steps[i].message);
                                } else {
                                    updateProgress(i, 'failed', data.steps[i].message || 'Failed');
                                }
                            }
                            // Mark the next step as failed (the one that threw the error)
                            if (data.steps.length < progressItems.length) {
                                updateProgress(data.steps.length, 'failed', data.error || 'Failed');
                            }
                        } else {
                            updateProgress(0, 'failed', data.error || 'Installation failed');
                        }

                        // Show error below progress
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'error-message active';
                        errorDiv.style.marginTop = '20px';
                        let errorHtml = '<i class="fas fa-exclamation-triangle"></i> <strong>Installation failed:</strong><br>' + (data.error || 'Unknown error');
                        if (data.file) {
                            errorHtml += '<br><small style="opacity: 0.7;">Location: ' + data.file + '</small>';
                        }
                        errorDiv.innerHTML = errorHtml;
                        document.getElementById('progress-list').after(errorDiv);
                    }
                } catch (error) {
                    console.error('Installation error:', error);

                    // Try to get more info from the response
                    let errorMessage = error.message || 'Network error';

                    // Mark first pending/running step as failed
                    let foundRunning = false;
                    progressItems.forEach((item, index) => {
                        if (!foundRunning && (item.classList.contains('running') || item.classList.contains('pending'))) {
                            updateProgress(index, 'failed', errorMessage);
                            foundRunning = true;
                        }
                    });

                    // Show error below progress
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message active';
                    errorDiv.style.marginTop = '20px';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> <strong>Installation failed:</strong><br>' + errorMessage + '<br><small style="opacity: 0.7;">Check Laravel logs for details: storage/logs/laravel.log</small>';
                    document.getElementById('progress-list').after(errorDiv);
                }
            });
        });
    </script>
</body>
</html>
