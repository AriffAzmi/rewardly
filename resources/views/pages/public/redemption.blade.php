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
            padding-bottom: 2rem;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
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

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Redemption Card */
        .redemption-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out 0.6s both;
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

        .card-icon {
            font-size: 4rem;
            background: linear-gradient(135deg, #c4362f 0%, #e85d4f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: bounce 2s infinite;
        }

        .voucher-input {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            background: #f7fafc;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .voucher-input:focus {
            border-color: #c4362f;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(196, 54, 47, 0.1);
        }

        .voucher-input.is-valid {
            border-color: #48bb78;
        }

        .voucher-input.is-invalid {
            border-color: #f56565;
            animation: shake 0.5s;
        }

        .input-icon {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.3rem;
            transition: all 0.3s ease;
        }

        .input-icon.valid {
            color: #48bb78;
        }

        .input-icon.invalid {
            color: #f56565;
        }

        .barcode-container {
            background: #ffffff;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .barcode-container svg {
            max-width: 100%;
            height: auto;
        }

        .timer-countdown.warning {
            color: #dd6b20;
            animation: pulse 1s infinite;
        }

        .timer-countdown.danger {
            color: #e53e3e;
            animation: pulse 0.5s infinite;
        }

        .redeem-btn {
            background: linear-gradient(135deg, #c4362f 0%, #e85d4f 100%);
            border: none;
            border-radius: 16px;
            color: #ffffff;
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
            transition: transform 0.3s ease;
        }

        .redeem-btn:hover:not(:disabled) i {
            transform: translateX(5px);
        }

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

        .feedback-message {
            border-radius: 12px;
            animation: fadeIn 0.3s ease;
            border-left-width: 4px;
        }

        .feedback-message.error {
            background: #fed7d7;
            color: #c53030;
            border-left-color: #f56565;
        }

        .feedback-message.success {
            background: #c6f6d5;
            color: #2f855a;
            border-left-color: #48bb78;
        }
    </style>
</head>
<body>
    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top" style="backdrop-filter: blur(10px);">
        <div class="container">
            <a class="navbar-brand mx-auto" href="#">
                <img src="{{ asset('assets/images/logo-red.png') }}" alt="Rewardly" class="img-fluid" style="max-width: 200px; max-height: 50px;">
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-11 col-md-8 col-lg-6 col-xl-5">
                    <!-- Redemption Card -->
                    <div class="redemption-card p-4 p-md-5">
                        <div class="text-center">
                            <div class="card-icon mb-4">
                                <i class="fas fa-gift"></i>
                            </div>

                            @if($error)
                            <div class="alert alert-danger feedback-message error mb-4" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
                            </div>
                            @endif

                            @if($success)
                            <div class="alert alert-success feedback-message success mb-4" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ $success }}
                            </div>
                            @endif

                            @if(!$voucher)
                            <!-- Voucher Code Display (Auto-filled) -->
                            <h2 class="h3 h2-md fw-semibold text-dark mb-2">Ready to Redeem</h2>
                            <p class="text-muted mb-4">Your voucher code has been verified and is ready to use</p>
                            
                            <div class="position-relative mb-4">
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg voucher-input is-valid py-3 px-4 pe-5" 
                                    value="{{ $code }}"
                                    readonly>
                                <i class="fas fa-check-circle input-icon valid"></i>
                            </div>

                            <form action="{{ route('redemption.submit') }}" method="POST" id="redemptionForm">
                                @csrf
                                <input type="hidden" name="code" value="{{ $code }}">
                                <button type="submit" class="btn btn-lg redeem-btn w-100 py-3" id="redeemBtn">
                                    <span>Redeem Voucher Now</span>
                                    <i class="fas fa-gift ms-2"></i>
                                </button>
                            </form>
                            @else
                            <!-- Voucher Redeemed with Barcode and Timer -->
                            <h2 class="h3 h2-md fw-semibold text-success mb-2">
                                <i class="fas fa-check-circle me-2"></i>Voucher Redeemed!
                            </h2>
                            <p class="text-muted mb-4">Your voucher is now active. Show this barcode at checkout</p>

                            <!-- Barcode Section -->
                            <div class="card border-2 border-light rounded-4 p-3 p-md-4 mb-4">
                                <div class="text-uppercase text-muted fw-semibold small mb-3" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-barcode me-2"></i>SCAN TO REDEEM
                                </div>
                                <div class="barcode-container p-2 p-md-3 mb-2" id="barcodeWrapper">
                                    <svg id="barcode"></svg>
                                </div>
                                <div class="fw-semibold text-dark small" style="font-family: 'Courier New', monospace; letter-spacing: 2px; word-break: break-all;">
                                    {{ $voucher->code }}
                                </div>
                            </div>

                            <!-- Timer Display -->
                            @if($timeRemaining !== null && $timeRemaining > 0)
                            <div class="card bg-danger-subtle border-2 border-danger-subtle rounded-4 p-3 p-md-4 mb-4">
                                <div class="text-uppercase text-danger fw-semibold small mb-2" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-clock me-2"></i>Voucher valid for
                                </div>
                                <div class="display-6 fw-bold text-danger timer-countdown" id="timerCountdown" style="font-family: 'Courier New', monospace;">
                                    {{ gmdate('i:s', $timeRemaining) }}
                                </div>
                            </div>
                            @endif

                            <!-- Voucher Details -->
                            <div class="card bg-light rounded-4 p-3 p-md-4 mb-4" style="border: 2px dashed #cbd5e0;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                            <span class="text-muted small">Voucher Code</span>
                                            <span class="fw-semibold text-dark small">{{ $voucher->code }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                            <span class="text-muted small">Description</span>
                                            <span class="fw-semibold text-dark small text-end">{{ $voucher->description }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                            <span class="text-muted small">Value</span>
                                            <span class="fw-semibold text-dark small">RM {{ number_format($voucher->retail_price, 2) }}</span>
                                        </div>
                                    </div>
                                    @if($voucher->discount_percentage > 0)
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                                            <span class="text-muted small">Discount</span>
                                            <span class="fw-semibold text-success small">{{ $voucher->discount_percentage }}% OFF</span>
                                        </div>
                                    </div>
                                    @endif
                                    @if($voucher->redeemed_at)
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">Redeemed At</span>
                                            <span class="fw-semibold text-dark small">
                                                @if(is_string($voucher->redeemed_at))
                                                    {{ \Carbon\Carbon::parse($voucher->redeemed_at)->format('d M Y H:i') }}
                                                @else
                                                    {{ $voucher->redeemed_at->format('d M Y H:i') }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <a href="{{ route('redemption.form') }}" class="btn btn-secondary btn-lg w-100 rounded-4 py-3">
                                <i class="fas fa-home me-2"></i>Redeem Another Voucher
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <p class="text-center text-white-50 mb-0 small">
                &copy; 2025 Rewardly. All rights reserved | AriffAzmi | 2025-10-16 10:29:19
            </p>
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
                timestamp: '2025-10-16 10:29:19'
            });
        });
    </script>
    @endif

    @if($voucher)
    <!-- Barcode Generation - Responsive -->
    <script>
        function calculateBarcodeWidth() {
            const wrapper = document.getElementById('barcodeWrapper');
            const wrapperWidth = wrapper.offsetWidth - 32;
            const screenWidth = window.innerWidth;
            
            let width = 2;
            let height = 60;
            
            if (screenWidth < 576) {
                width = 1.5;
                height = 50;
            } else if (screenWidth < 768) {
                width = 1.8;
                height = 55;
            }
            
            return { width, height };
        }

        function generateBarcode() {
            const { width, height } = calculateBarcodeWidth();
            
            try {
                JsBarcode("#barcode", "{{ $voucher->code }}", {
                    format: "CODE128",
                    width: width,
                    height: height,
                    displayValue: false,
                    background: "#ffffff",
                    lineColor: "#000000",
                    margin: 5
                });

                console.log('Barcode generated', {
                    voucher_code: '{{ $voucher->code }}',
                    width: width,
                    height: height,
                    screen_width: window.innerWidth,
                    user_login: 'AriffAzmi',
                    timestamp: '2025-10-16 10:29:19'
                });
            } catch (error) {
                console.error('Barcode generation failed:', error);
            }
        }

        generateBarcode();

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(generateBarcode, 250);
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
            timestamp: '2025-10-16 10:29:19'
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

            if (timeRemaining <= 300 && timeRemaining > 60) {
                timerCountdown.classList.add('warning');
                timerCountdown.classList.remove('danger');
            } else if (timeRemaining <= 60) {
                timerCountdown.classList.add('danger');
                timerCountdown.classList.remove('warning');
            }

            timeRemaining--;
        }

        const interval = setInterval(updateTimer, 1000);
        updateTimer();

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