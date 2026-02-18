<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment · success</title>
    <!-- minimal, clean, responsive — no external dependencies -->
    <style>
        /* GLOBAL RESET & BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #f6f9fc 0%, #e9f1f8 100%);
            min-height: 100vh;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            margin: 0;
        }

        /* main container – holds both cards side by side (or stacked) */
        .payment-demo {
            display: flex;
            flex-wrap: wrap;
            gap: 1.8rem;
            max-width: 900px;
            width: 100%;
            justify-content: center;
        }

        /* base card style */
        .card {
            flex: 1 1 280px;
            min-width: 260px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            background: #ffffff;
            border-radius: 2rem;
            padding: 2rem 1.5rem 2rem 1.5rem;
            box-shadow:
                0 20px 35px -8px rgba(0, 20, 30, 0.15),
                0 4px 8px rgba(0, 0, 0, 0.02);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 28px 40px -12px rgba(0, 40, 60, 0.2);
        }

        /* subtle distinction between success / cancel */
        .card.success {
            border-top: 5px solid #10b981;
            /* emerald green */
        }

        .card.cancel {
            border-top: 5px solid #f43f5e;
            /* rose / red */
        }

        /* icons – simple characters, big & expressive */
        .icon {
            font-size: 4rem;
            line-height: 1;
            margin-bottom: 0.75rem;
            display: inline-block;
            background: rgba(0, 0, 0, 0.02);
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
            border-radius: 50%;
            background: #f8fafc;
            box-shadow: inset 0 -2px 5px rgba(255, 255, 255, 0.8), inset 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .success .icon {
            color: #10b981;
            background: #e9f9f0;
        }

        .cancel .icon {
            color: #f43f5e;
            background: #fff1f3;
        }

        /* headings */
        h2 {
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }

        .success h2 {
            color: #0b4f3c;
        }

        .cancel h2 {
            color: #881337;
        }

        /* subtitle / transaction message */
        .message {
            font-size: 1rem;
            color: #2c3e50;
            margin-bottom: 1.8rem;
            padding: 0 0.25rem;
            opacity: 0.8;
            font-weight: 400;
        }

        /* tiny detail row (like order id or time) */
        .detail-row {
            display: flex;
            justify-content: center;
            gap: 0.6rem 1rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            background: #f1f5f9;
            padding: 0.6rem 1rem;
            border-radius: 60px;
            margin: 1.5rem 0 1.2rem 0;
            color: #1e293b;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #94a3b8;
            margin: 0 0.25rem;
        }

        /* action buttons */
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1.5rem;
            justify-content: center;
        }

        .btn {
            border: none;
            background: #ffffff;
            padding: 0.7rem 1.3rem;
            border-radius: 3rem;
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
            border: 1px solid #dbe0e6;
            color: #1e293b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            flex: 1 1 auto;
            min-width: 120px;
        }

        .btn-primary {
            background: #0f172a;
            border: 1px solid #0f172a;
            color: white;
            box-shadow: 0 8px 18px -8px #0f172a80;
        }

        .btn-primary:hover {
            background: #1e293b;
            border-color: #1e293b;
        }

        .btn-secondary {
            background: white;
            border: 1px solid #cbd5e1;
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        /* tiny badge for demo context */
        .demo-hint {
            font-size: 0.75rem;
            margin-top: 1.2rem;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 1rem;
            letter-spacing: 0.2px;
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }

        .demo-hint span {
            background: #ecfdf3;
            padding: 0.2rem 0.5rem;
            border-radius: 30px;
            color: #0b4f3c;
            font-weight: 450;
        }

        .cancel .demo-hint span {
            background: #fff0f3;
            color: #881337;
        }

        /* responsive fine-tuning */
        @media (max-width: 650px) {
            .payment-demo {
                gap: 1.2rem;
            }

            .card {
                padding: 1.8rem 1.2rem;
            }

            h2 {
                font-size: 1.9rem;
            }

            .icon {
                width: 80px;
                height: 80px;
                font-size: 3rem;
            }
        }

        @media (max-width: 380px) {
            .detail-row {
                flex-direction: column;
                gap: 0.25rem;
                border-radius: 24px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        /* optional very minimal "logo" */
        .minimal-emoji-logo {
            font-size: 1.8rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.05));
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="payment-demo">
        <!-- ⬇️ PAYMENT SUCCESS CARD (minimal) -->
        <div class="card success">
            <div class="minimal-emoji-logo">⚡︎ pay</div>
            <div class="icon">✓</div>
            <h2>Success</h2>
            <div class="message">Your payment has been processed.</div>

            <!-- tiny meta row (fake details) -->
            <div class="detail-row">
                <span class="detail-item">💳 •••4242</span>
                <span class="dot"></span>
                <span class="detail-item">💰 $49.00</span>
                <span class="dot"></span>
                <span class="detail-item">📅 18 Feb</span>
            </div>

            <!-- action buttons (minimal) -->
            <div class="actions">
                <button class="btn btn-secondary" onclick="alert('📨 receipt sent (demo)')">📧 Receipt</button>
                <button class="btn btn-primary" onclick="alert('↻ back to dashboard (demo)')">Dashboard</button>
            </div>

            <!-- tiny hint for demo context -->
            <div class="demo-hint">
                <span>payment success</span>
                <span style="background:#e9f9f0;">#confirmed</span>
            </div>
        </div>
    </div>
</body>

</html>
