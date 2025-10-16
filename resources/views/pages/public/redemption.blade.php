<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rewardly - Redeem Your Voucher & Claim Rewards Instantly</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: #f8f9fa;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #c4362f 0%, #e85d4f 100%);
            overflow: hidden;
            padding-top: 80px;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><defs><linearGradient id="gift-grad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.1);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0.05);stop-opacity:1" /></linearGradient></defs><g fill="url(%23gift-grad)"><circle cx="100" cy="100" r="40" opacity="0.3"/><rect x="300" y="80" width="60" height="60" rx="8" opacity="0.2"/><circle cx="900" cy="150" r="50" opacity="0.25"/><rect x="700" y="400" width="70" height="70" rx="10" opacity="0.2"/><circle cx="200" cy="450" r="45" opacity="0.3"/><rect x="1000" y="200" width="55" height="55" rx="8" opacity="0.25"/><path d="M500,300 L520,280 L540,300 L520,320 Z" opacity="0.2"/><path d="M150,250 L170,230 L190,250 L170,270 Z" opacity="0.25"/></g></svg>');
            background-size: cover;
            background-position: center;
            opacity: 0.6;
            animation: floatBackground 20s ease-in-out infinite;
        }

        @keyframes floatBackground {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1rem;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.95);
            margin-bottom: 3rem;
            font-weight: 300;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        /* Redemption Card */
        .redemption-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            max-width: 550px;
            margin: 0 auto;
            animation: fadeInUp 0.8s ease-out 0.6s both;
            position: relative;
            overflow: hidden;
        }

        .redemption-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(196, 54, 47, 0.05), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .card-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, #c4362f 0%, #e85d4f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .card-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .card-description {
            color: #718096;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .voucher-input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .voucher-input {
            width: 100%;
            padding: 1.2rem 3.5rem 1.2rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1.1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: #f7fafc;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .voucher-input:focus {
            outline: none;
            border-color: #c4362f;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(196, 54, 47, 0.1);
        }

        .voucher-input.is-invalid {
            border-color: #f56565;
            animation: shake 0.5s;
        }

        .voucher-input.is-valid {
            border-color: #48bb78;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .input-icon {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3rem;
            color: #a0aec0;
            transition: all 0.3s ease;
        }

        .input-icon.valid {
            color: #48bb78;
        }

        .input-icon.invalid {
            color: #f56565;
        }

        /* Barcode Section */
        .barcode-section {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .barcode-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
        }

        .barcode-container {
            background: #ffffff;
            padding: 1rem;
            border-radius: 8px;
            display: inline-block;
            margin-bottom: 0.5rem;
        }

        .barcode-code {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3748;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin-top: 0.5rem;
        }

        /* Timer Display */
        .timer-display {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .timer-label {
            font-size: 0.85rem;
            color: #742a2a;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .timer-countdown {
            font-size: 2rem;
            font-weight: 700;
            color: #c53030;
            font-family: 'Courier New', monospace;
        }

        .timer-countdown.warning {
            color: #dd6b20;
            animation: pulse 1s infinite;
        }

        .timer-countdown.danger {
            color: #e53e3e;
            animation: pulse 0.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Voucher Details Box */
        .voucher-details-box {
            background: #f7fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px dashed #cbd5e0;
        }

        .voucher-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .voucher-detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #718096;
            font-size: 0.9rem;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .redeem-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #c4362f 0%, #e85d4f 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(196, 54, 47, 0.3);
        }

        .redeem-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .redeem-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(196, 54, 47, 0.4);
        }

        .redeem-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .redeem-btn:disabled {
            background: #cbd5e0;
            cursor: not-allowed;
        }

        .redeem-btn i {
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .redeem-btn:hover:not(:disabled) i {
            transform: translateX(5px);
        }

        .btn-secondary-cancel {
            width: 100%;
            padding: 1rem;
            background: #6c757d;
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-secondary-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .feedback-message {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .feedback-message.error {
            background: #fed7d7;
            color: #c53030;
            border-left: 4px solid #f56565;
        }

        .feedback-message.success {
            background: #c6f6d5;
            color: #2f855a;
            border-left: 4px solid #48bb78;
        }

        /* Loading Animation */
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .loading .redeem-btn::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Footer */
        .footer {
            background: #2d3748;
            color: #ffffff;
            padding: 2rem 0;
            text-align: center;
        }

        .footer-text {
            color: #a0aec0;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .redemption-card {
                padding: 2rem 1.5rem;
                margin: 0 1rem;
            }

            .card-icon {
                font-size: 3rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 1.8rem;
            }

            .card-title {
                font-size: 1.4rem;
            }

            .voucher-input {
                font-size: 1rem;
                padding: 1rem 3rem 1rem 1rem;
            }

            .timer-countdown {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <nav style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 1rem 0; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
        <div class="container">
            <div style="display: flex; align-items: center; justify-content: center;">
                <img src="{{ asset('assets/images/logo-red.png') }}" alt="" style="width: 200px;height: 50px;">
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content mt-4">
                {{-- <h1 class="hero-title">Redeem Your Voucher Now!</h1> --}}
                <p class="hero-subtitle">Click the button below to claim your exclusive reward</p>
                
                <!-- Redemption Card -->
                <div class="redemption-card mb-3">
                    <div class="card-icon">
                        <i class="fas fa-gift"></i>
                    </div>

                    @if($error)
                    <div class="feedback-message error">
                        <i class="fas fa-exclamation-circle"></i> {{ $error }}
                    </div>
                    @endif

                    @if($success)
                    <div class="feedback-message success">
                        <i class="fas fa-check-circle"></i> {{ $success }}
                    </div>
                    @endif

                    @if(!$voucher)
                    <!-- Voucher Code Display (Auto-filled) -->
                    <h2 class="card-title">Ready to Redeem</h2>
                    <p class="card-description">Your voucher code has been verified and is ready to use</p>
                    
                    <div class="voucher-input-group">
                        <input 
                            type="text" 
                            class="voucher-input is-valid" 
                            value="{{ $code }}"
                            readonly>
                        <i class="fas fa-check-circle input-icon valid"></i>
                    </div>

                    <form action="{{ route('redemption.submit') }}" method="POST" id="redemptionForm">
                        @csrf
                        <input type="hidden" name="code" value="{{ $code }}">
                        <button type="submit" class="redeem-btn" id="redeemBtn">
                            <span>Redeem Voucher Now</span>
                            <i class="fas fa-gift"></i>
                        </button>
                    </form>
                    @else
                    <!-- Voucher Redeemed with Barcode and Timer -->
                    <h2 class="card-title" style="color: #48bb78;">
                        <i class="fas fa-check-circle"></i> Voucher Redeemed!
                    </h2>
                    <p class="card-description">Your voucher is now active. Show this barcode at checkout</p>

                    <!-- Barcode Section -->
                    <div class="barcode-section">
                        <div class="barcode-label">
                            <i class="fas fa-barcode"></i> Scan to Redeem
                        </div>
                        <div class="barcode-container">
                            <svg id="barcode" width="300" height="80"></svg>
                        </div>
                        <div class="barcode-code">{{ $voucher->code }}</div>
                    </div>

                    <!-- Timer Display -->
                    @if($timeRemaining !== null && $timeRemaining > 0)
                    <div class="timer-display">
                        <div class="timer-label">
                            <i class="fas fa-clock"></i> Voucher valid for
                        </div>
                        <div class="timer-countdown" id="timerCountdown">
                            {{ gmdate('i:s', $timeRemaining) }}
                        </div>
                    </div>
                    @endif

                    <!-- Voucher Details -->
                    <div class="voucher-details-box">
                        <div class="voucher-detail-row">
                            <span class="detail-label">Voucher Code</span>
                            <span class="detail-value">{{ $voucher->code }}</span>
                        </div>
                        <div class="voucher-detail-row">
                            <span class="detail-label">Description</span>
                            <span class="detail-value">{{ $voucher->description }}</span>
                        </div>
                        <div class="voucher-detail-row">
                            <span class="detail-label">Value</span>
                            <span class="detail-value">RM {{ number_format($voucher->retail_price, 2) }}</span>
                        </div>
                        @if($voucher->discount_percentage > 0)
                        <div class="voucher-detail-row">
                            <span class="detail-label">Discount</span>
                            <span class="detail-value" style="color: #48bb78;">{{ $voucher->discount_percentage }}% OFF</span>
                        </div>
                        @endif
                        @if($voucher->redeemed_at)
                        <div class="voucher-detail-row">
                            <span class="detail-label">Redeemed At</span>
                            <span class="detail-value">
                                @if(is_string($voucher->redeemed_at))
                                    {{ \Carbon\Carbon::parse($voucher->redeemed_at)->format('d M Y H:i') }}
                                @else
                                    {{ $voucher->redeemed_at->format('d M Y H:i') }}
                                @endif
                            </span>
                        </div>
                        @endif
                    </div>

                    <a href="{{ route('redemption.form') }}" class="btn-secondary-cancel" style="display: block; text-align: center; text-decoration: none;">
                        <i class="fas fa-home"></i> Redeem Another Voucher
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p class="footer-text">&copy; 2025 Rewardly. All rights reserved | AriffAzmi | 2025-10-16 08:34:33</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JsBarcode Library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    @if(!$voucher)
    <!-- Form submission with loading state -->
    <script>
        const redemptionForm = document.getElementById('redemptionForm');
        const redeemBtn = document.getElementById('redeemBtn');

        redemptionForm.addEventListener('submit', function() {
            redeemBtn.disabled = true;
            redemptionForm.classList.add('loading');
            redeemBtn.innerHTML = '<span>Redeeming...</span>';

            console.log('Voucher redemption initiated', {
                voucher_code: '{{ $code }}',
                user_login: 'AriffAzmi',
                timestamp: '2025-10-16 08:34:33'
            });
        });
    </script>
    @endif

    @if($voucher)
    <!-- Barcode Generation -->
    <script>
        // Generate barcode
        JsBarcode("#barcode", "{{ $voucher->code }}", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: false,
            background: "#ffffff",
            lineColor: "#000000",
            margin: 10
        });

        console.log('Barcode generated', {
            voucher_code: '{{ $voucher->code }}',
            user_login: 'AriffAzmi',
            timestamp: '2025-10-16 08:34:33'
        });
    </script>

    @if($timeRemaining !== null && $timeRemaining > 0)
    <!-- Timer JavaScript -->
    <script>
        let timeRemaining = {{ $timeRemaining }};
        const voucherCode = '{{ $voucher->code }}';
        const timerCountdown = document.getElementById('timerCountdown');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        console.log('Voucher redemption timer started', {
            voucher_code: voucherCode,
            time_remaining: timeRemaining,
            user_login: 'AriffAzmi',
            timestamp: '2025-10-16 08:34:33'
        });

        function updateTimer() {
            if (timeRemaining <= 0) {
                timerCountdown.textContent = '00:00';
                timerCountdown.classList.add('danger');
                return;
            }

            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            const display = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            timerCountdown.textContent = display;

            // Add warning classes
            if (timeRemaining <= 300 && timeRemaining > 60) {
                timerCountdown.classList.add('warning');
                timerCountdown.classList.remove('danger');
            } else if (timeRemaining <= 60) {
                timerCountdown.classList.add('danger');
                timerCountdown.classList.remove('warning');
            }

            timeRemaining--;
        }

        // Update timer every second
        const interval = setInterval(updateTimer, 1000);
        updateTimer();

        // Check with server every 30 seconds
        setInterval(() => {
            fetch('{{ route('redemption.check-timer') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ code: voucherCode })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success || data.expired) {
                    clearInterval(interval);
                    timerCountdown.textContent = '00:00';
                    timerCountdown.classList.add('danger');
                } else {
                    timeRemaining = data.time_remaining;
                }
            })
            .catch(error => console.error('Timer check failed:', error));
        }, 30000);
    </script>
    @endif
    @endif
</body>
</html>