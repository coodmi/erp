@php
    $settings = \App\Models\Utility::settings();
    $logo = asset(Storage::url('uploads/logo/'));
    $company_logo = \App\Models\Utility::getValByName('company_logo_dark');

    $aid  = isset($_GET['moneyrecipt']) ? intval($_GET['moneyrecipt']) : null;
    $clid = isset($_GET['client_id'])   ? intval($_GET['client_id'])   : null;
    $results = [];
    $agent_name = 'Unknown';
    $inv_id = 'RCP-' . strtoupper(\Str::random(8));

    try {
        $connection = DB::connection();
        if ($aid && $clid) {
            $results = $connection->select("
                SELECT clients.*, countries.country_name
                FROM clients
                LEFT JOIN countries ON clients.visa_country_id = countries.id
                WHERE clients.agent_id = $aid AND clients.id = $clid
            ");
            $agentRow = $connection->select("SELECT agent_name FROM agents WHERE id = $aid");
            $agent_name = !empty($agentRow) ? $agentRow[0]->agent_name : 'Unknown';
        }
    } catch (\Exception $e) {}

    $client = !empty($results) ? $results[0] : null;
    $visaLabels = ['WV'=>'Work Permit Visa','SV'=>'Student Visa','TV'=>'Tourist Visa','BV'=>'Business Visa','OV'=>'Others'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Receipt — {{ $client->client_name ?? 'Client' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Segoe UI',Arial,sans-serif; background:#f1f5f9; color:#1e293b; }
        .screen-wrap { max-width:900px; margin:32px auto; padding:0 16px 48px; }
        .screen-actions { display:flex; gap:10px; justify-content:flex-end; margin-bottom:20px; }
        .btn-print { display:inline-flex; align-items:center; gap:7px; padding:10px 22px; border-radius:12px; font-weight:700; font-size:.88rem; border:0; cursor:pointer; transition:all .18s; }
        .btn-print.primary { background:linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 8px 18px rgba(37,99,235,.25); }
        .btn-print.secondary { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
        .btn-print:hover { filter:brightness(1.05); transform:translateY(-1px); }
        .receipt { background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(15,23,42,.12); overflow:hidden; }
        .receipt-header { background:linear-gradient(135deg,#1e3a8a,#1d4ed8,#2563eb); padding:28px 36px; display:flex; align-items:center; justify-content:space-between; }
        .receipt-header img { height:52px; width:auto; object-fit:contain; filter:brightness(0) invert(1); }
        .receipt-title h1 { color:#fff; font-size:2rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; margin:0; text-align:right; }
        .receipt-title p { color:rgba(255,255,255,.75); font-size:.82rem; margin-top:4px; text-align:right; }
        .receipt-meta { background:#f8fafc; border-bottom:1px solid #e2e8f0; padding:14px 36px; display:flex; gap:32px; flex-wrap:wrap; }
        .receipt-meta-item .lbl { font-size:.68rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.07em; display:block; }
        .receipt-meta-item .val { font-size:.88rem; font-weight:700; color:#1e293b; }
        .receipt-body { padding:28px 36px; }
        .bill-section { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:28px; }
        .bill-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px 20px; }
        .bill-box h6 { font-size:.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:10px; }
        .bill-box p { font-size:.88rem; color:#334155; line-height:1.7; margin:0; }
        .services-title { font-size:.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px; }
        .services-table { width:100%; border-collapse:separate; border-spacing:0; border-radius:14px; overflow:hidden; border:1px solid #e2e8f0; margin-bottom:24px; }
        .services-table thead th { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; padding:12px 16px; text-align:left; }
        .services-table tbody td { padding:13px 16px; font-size:.88rem; color:#334155; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .services-table tbody tr:last-child td { border-bottom:0; }
        .price-input { border:none; background:transparent; text-align:right; font-size:.88rem; font-weight:700; color:#059669; width:100px; outline:none; }
        .totals-wrap { display:flex; justify-content:flex-end; margin-bottom:28px; }
        .totals-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px 24px; min-width:280px; }
        .totals-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #f1f5f9; font-size:.88rem; }
        .totals-row:last-child { border-bottom:0; }
        .totals-row .t-lbl { color:#64748b; font-weight:600; }
        .totals-row .t-val { font-weight:800; color:#0f172a; }
        .totals-input { border:none; background:transparent; text-align:right; font-size:.88rem; font-weight:800; color:#0f172a; width:120px; outline:none; }
        .receipt-notice { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:10px 16px; text-align:center; font-size:.78rem; color:#1d4ed8; font-weight:600; margin-bottom:24px; }
        .sig-section { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:8px; }
        .sig-box { text-align:center; }
        .sig-line { height:1px; background:#cbd5e1; margin:0 20px 10px; }
        .sig-box p { font-size:.78rem; color:#64748b; font-weight:600; }
        .receipt-footer { background:linear-gradient(135deg,#1e3a8a,#1d4ed8); padding:20px 36px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
        .receipt-footer img { height:40px; width:auto; object-fit:contain; filter:brightness(0) invert(1); }
        .receipt-footer-info { text-align:right; }
        .receipt-footer-info p { color:rgba(255,255,255,.85); font-size:.78rem; margin:2px 0; }
        @media print {
            body { background:#fff; }
            .screen-actions { display:none !important; }
            .screen-wrap { margin:0; padding:0; max-width:100%; }
            .receipt { border-radius:0; box-shadow:none; }
        }
        @media(max-width:600px){
            .bill-section,.sig-section { grid-template-columns:1fr; }
            .receipt-header { flex-direction:column; gap:12px; }
            .receipt-title h1,.receipt-title p { text-align:center; }
        }
    </style>
</head>
<body>
<div class="screen-wrap">
    <div class="screen-actions">
        <button class="btn-print secondary" onclick="window.history.back()">← Back</button>
        <button class="btn-print primary" onclick="window.print()">🖨 Print Receipt</button>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <img src="{{ $logo . '/' . ($company_logo ?: 'logo-dark.png') }}" alt="Logo">
            <div class="receipt-title">
                <h1>Money Receipt</h1>
                <p>{{ $settings['company_name'] ?? 'Eraz-ehan International' }}</p>
            </div>
        </div>

        <div class="receipt-meta">
            <div class="receipt-meta-item">
                <span class="lbl">Receipt No</span>
                <span class="val">{{ $inv_id }}</span>
            </div>
            <div class="receipt-meta-item">
                <span class="lbl">Date</span>
                <span class="val">{{ date('d M Y') }}</span>
            </div>
            <div class="receipt-meta-item">
                <span class="lbl">Agent</span>
                <span class="val">{{ $agent_name }}</span>
            </div>
            @if($client)
            <div class="receipt-meta-item">
                <span class="lbl">Client ID</span>
                <span class="val">{{ $client->unique_code ?? '—' }}</span>
            </div>
            @endif
        </div>

        <div class="receipt-body">
            @if($client)
            <div class="bill-section">
                <div class="bill-box">
                    <h6>Bill To</h6>
                    <p>
                        <strong>{{ $client->client_name }}</strong><br>
                        Passport: <strong>{{ $client->passport_no ?? '—' }}</strong><br>
                        Address: {{ $client->address ?? '—' }}<br>
                        Country: {{ $client->country_name ?? '—' }}
                    </p>
                </div>
                <div class="bill-box">
                    <h6>Visa Details</h6>
                    <p>
                        Type: <strong>{{ $visaLabels[$client->visa_type] ?? $client->visa_type }}</strong><br>
                        Country: <strong>{{ $client->country_name ?? '—' }}</strong><br>
                        Agent: <strong>{{ $agent_name }}</strong>
                    </p>
                </div>
            </div>

            <p class="services-title">Services</p>
            <table class="services-table" id="servicesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Visa Type</th>
                        <th>Country</th>
                        <th style="text-align:right;">Unit Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $index => $result)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $visaLabels[$result->visa_type] ?? $result->visa_type }}</td>
                        <td>{{ $result->country_name }}</td>
                        <td style="text-align:right;">
                            <input type="number" class="price-input unit-price" onkeyup="recalculate()" value="{{ $result->unit_price ?? 0 }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="totals-wrap">
                <div class="totals-box">
                    <div class="totals-row">
                        <span class="t-lbl">Subtotal</span>
                        <span class="t-val"><input type="number" class="totals-input" id="subtotal" value="{{ $client->unit_price ?? 0 }}" readonly></span>
                    </div>
                    <div class="totals-row">
                        <span class="t-lbl">Paid</span>
                        <span class="t-val" style="color:#059669;">
                            <input type="number" class="totals-input" id="paid" onkeyup="recalculate()" value="{{ intval($client->amount_paid ?? 0) }}" style="color:#059669;">
                        </span>
                    </div>
                    <div class="totals-row">
                        <span class="t-lbl">Due</span>
                        <span class="t-val" style="color:#e11d48;">
                            <input type="number" class="totals-input" id="due" value="{{ intval($client->amount_due ?? 0) }}" readonly style="color:#e11d48;">
                        </span>
                    </div>
                    <div class="totals-row">
                        <span class="t-lbl">Refund</span>
                        <span class="t-val" style="color:#d97706;">{{ number_format($client->refund ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="receipt-notice">
                Money receipts will not be considered valid without the MD's seal and signature.
            </div>

            <div class="sig-section">
                <div class="sig-box"><div class="sig-line"></div><p>Cashier Signature</p></div>
                <div class="sig-box"><div class="sig-line"></div><p>Manager Signature</p></div>
                <div class="sig-box"><div class="sig-line"></div><p>MD Signature & Seal</p></div>
            </div>
            @else
                <div style="text-align:center;padding:48px;color:#94a3b8;"><p>No client data found.</p></div>
            @endif
        </div>

        <div class="receipt-footer">
            <div>
                <img src="{{ $logo . '/' . ($company_logo ?: 'logo-dark.png') }}" alt="Logo">
                <p style="color:rgba(255,255,255,.75);font-size:.75rem;margin-top:6px;">{{ $settings['company_address'] ?? '' }}</p>
            </div>
            <div class="receipt-footer-info">
                <p><strong style="color:#fff;">{{ $settings['company_name'] ?? 'Eraz-ehan International' }}</strong></p>
                <p>{{ $settings['company_telephone'] ?? '' }}</p>
                <p>{{ $settings['mail_from_address'] ?? '' }}</p>
            </div>
        </div>
    </div>
</div>
<script>
function recalculate() {
    var subtotal = 0;
    document.querySelectorAll('.unit-price').forEach(function(inp) { subtotal += parseFloat(inp.value) || 0; });
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    var paid = parseFloat(document.getElementById('paid').value) || 0;
    document.getElementById('due').value = Math.max(0, subtotal - paid).toFixed(2);
}
</script>
</body>
</html>
