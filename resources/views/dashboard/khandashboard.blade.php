@extends('layouts.admin')
@section('page-title'){{ __('Dashboard') }}@endsection

@php
/* ── Data Collection ── */
$agents=[];$clients=[];$vendors=[];$countries=[];$expenses=collect();
$cresults=[];$aresults=[];$vresults=[];
$ctotpaid=0;$ctotdue=0;$expsum=0;$totRefund=0;
$monthlyData=collect();$visaStats=[];

try {
    $db = DB::connection();
    $cresults   = $db->select("SELECT c.*,co.country_name FROM clients c LEFT JOIN countries co ON c.visa_country_id=co.id ORDER BY c.id DESC");
    $aresults   = $db->select("SELECT a.*,co.country_name FROM agents a LEFT JOIN countries co ON a.visa_country_id=co.id ORDER BY a.id DESC");
    $vresults   = $db->select("SELECT v.*,co.country_name FROM vendors v LEFT JOIN countries co ON v.visa_country_id=co.id ORDER BY v.id DESC");
    $agents     = $db->select("SELECT * FROM agents");
    $clients    = $db->select("SELECT * FROM clients");
    $vendors    = $db->select("SELECT * FROM vendors");
    $countries  = $db->select("SELECT * FROM countries");
    $ctotpaid   = $db->select("SELECT COALESCE(SUM(amount_paid),0) as v FROM clients")[0]->v;
    $ctotdue    = $db->select("SELECT COALESCE(SUM(amount_due),0) as v FROM clients")[0]->v;
    $totRefund  = $db->select("SELECT COALESCE(SUM(refund),0) as v FROM clients")[0]->v;
    $expsum     = $db->select("SELECT COALESCE(SUM(expense_amount),0) as v FROM vexpense")[0]->v;
    $expenses   = DB::table('vexpense')->select(DB::raw("DATE_FORMAT(expense_date,'%b %Y') as mo, SUM(expense_amount) as total"))->groupBy('mo')->orderByRaw("MIN(expense_date)")->limit(12)->get();
    $monthlyData= DB::table('clients')->select(DB::raw("DATE_FORMAT(created_at,'%b %Y') as mo, SUM(amount_paid) as paid, SUM(amount_due) as due"))->groupBy('mo')->orderByRaw("MIN(created_at)")->limit(12)->get();
    $visaStats  = $db->select("SELECT visa_type, COUNT(*) as cnt FROM clients GROUP BY visa_type ORDER BY cnt DESC");
} catch(\Exception $e){}

$netPaid  = $ctotpaid - $totRefund;
$totExp   = $expsum;
$revenue  = $netPaid - $totExp;
$fmt      = fn($v) => '৳'.number_format((float)$v, 2);

/* chart data */
$expMonths  = $expenses->pluck('mo')->toArray();
$expData    = $expenses->pluck('total')->map(fn($v)=>(float)$v)->toArray();
$paidMonths = $monthlyData->pluck('mo')->toArray();
$paidData   = $monthlyData->pluck('paid')->map(fn($v)=>(float)$v)->toArray();
$dueData    = $monthlyData->pluck('due')->map(fn($v)=>(float)$v)->toArray();
$visaMap    = ['WV'=>'Work Visa','SV'=>'Student Visa','TV'=>'Tourist Visa','BV'=>'Business Visa'];
$visaLabels = array_map(fn($v)=>$visaMap[$v->visa_type]??'Other', $visaStats);
$visaCounts = array_map(fn($v)=>(int)$v->cnt, $visaStats);

/* per-entity totals */
$totPaidA=0;$totDueA=0;$totPaidV=0;$totDueV=0;$totPaidC=0;$totDueC=0;
foreach($agents  as $r){$totPaidA+=$r->amount_paid;$totDueA+=$r->amount_due;}
foreach($vendors as $r){$totPaidV+=$r->amount_paid;$totDueV+=$r->amount_due;}
foreach($clients as $r){$totPaidC+=$r->amount_paid;$totDueC+=$r->amount_due;}
@endphp

@push('css-page')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
.kd *{font-family:'Inter',sans-serif}
/* KPI */
.kd-kpi{border:none!important;border-radius:16px!important;box-shadow:0 1px 8px rgba(0,0,0,.07)!important;transition:transform .2s,box-shadow .2s}
.kd-kpi:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.11)!important}
.kd-kpi .card-body{padding:1.25rem 1.4rem}
.kd-ico{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;color:#fff;flex-shrink:0}
.kd-lbl{font-size:.67rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px}
.kd-val{font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1.1}
.kd-sub{font-size:.71rem;font-weight:500;margin-top:3px}
.kd-up{color:#059669}.kd-dn{color:#e11d48}.kd-nt{color:#64748b}
/* gradients */
.gi{background:linear-gradient(135deg,#818cf8,#4f46e5)}
.ge{background:linear-gradient(135deg,#34d399,#059669)}
.ga{background:linear-gradient(135deg,#fbbf24,#d97706)}
.gr{background:linear-gradient(135deg,#fb7185,#e11d48)}
.gs{background:linear-gradient(135deg,#38bdf8,#0284c7)}
.gv{background:linear-gradient(135deg,#c084fc,#7c3aed)}
.gt{background:linear-gradient(135deg,#2dd4bf,#0d9488)}
.go{background:linear-gradient(135deg,#fb923c,#ea580c)}
/* banner */
.kd-banner{background:linear-gradient(135deg,#1a2035,#1e3a8a,#1d4ed8);border-radius:16px;padding:1.25rem 1.75rem;color:#fff;box-shadow:0 6px 24px rgba(37,99,235,.25)}
.kd-bitem{text-align:center;padding:.35rem .9rem}
.kd-bval{font-size:1.3rem;font-weight:800;line-height:1}
.kd-blbl{font-size:.62rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;opacity:.75;margin-top:3px}
.kd-bdiv{width:1px;background:rgba(255,255,255,.2);align-self:stretch;margin:.3rem 0}
/* chart cards */
.kd-chart{border:none!important;border-radius:16px!important;box-shadow:0 1px 8px rgba(0,0,0,.07)!important}
.kd-chart .card-header{background:#fff!important;border-bottom:1px solid #f1f5f9!important;border-radius:16px 16px 0 0!important;padding:.9rem 1.25rem;display:flex;align-items:center;gap:.6rem}
.kd-chart .card-header h6{margin:0;font-size:.9rem;font-weight:700;color:#0f172a}
.kd-chart .card-header small{font-size:.71rem;color:#94a3b8;margin-left:auto}
.kd-hico{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.88rem;color:#fff;flex-shrink:0}
/* tables */
.kd-tbl thead th{background:#f8fafc!important;font-size:.66rem!important;font-weight:700!important;letter-spacing:.08em!important;text-transform:uppercase!important;color:#64748b!important;border-bottom:2px solid #e2e8f0!important;padding:.6rem 1rem!important;white-space:nowrap}
.kd-tbl tbody td{padding:.6rem 1rem;font-size:.82rem;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9!important}
.kd-tbl tbody tr:last-child td{border-bottom:none!important}
.kd-tbl tbody tr:hover{background:#f8fafc!important}
/* badges */
.vb{display:inline-flex;align-items:center;gap:3px;padding:.18em .65em;border-radius:20px;font-size:.67rem;font-weight:700}
.vb::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor;opacity:.6}
.vb-wv{background:#ede9fe;color:#6d28d9}.vb-sv{background:#dbeafe;color:#1d4ed8}
.vb-tv{background:#dcfce7;color:#15803d}.vb-bv{background:#fef9c3;color:#a16207}.vb-ov{background:#f1f5f9;color:#475569}
.sb{display:inline-block;padding:.18em .65em;border-radius:20px;font-size:.67rem;font-weight:700}
.sb-a{background:#dcfce7;color:#15803d}.sb-p{background:#fef9c3;color:#a16207}.sb-i{background:#fee2e2;color:#b91c1c}
code.uid{color:#6366f1!important;background:#ede9fe;padding:1px 7px;border-radius:5px;font-size:.76rem}
/* section card */
.kd-sec{border:none!important;border-radius:16px!important;box-shadow:0 1px 8px rgba(0,0,0,.07)!important}
.kd-sec .card-header{background:#fff!important;border-bottom:1px solid #f1f5f9!important;border-radius:16px 16px 0 0!important;padding:.9rem 1.25rem;display:flex;align-items:center;gap:.6rem}
.kd-sec .card-header h6{margin:0;font-size:.9rem;font-weight:700;color:#0f172a}
/* Responsive */
.kd-banner{
    display:flex;
    flex-wrap:wrap;
    align-items:stretch;
    justify-content:space-between;
    padding:.5rem 0;
}
.kd-bdiv{display:none!important}
.kd-bitem{flex:1 1 12.5%;min-width:88px;padding:.35rem .25rem;text-align:center}
.kd-val{font-size:clamp(1.1rem,2.5vw,1.5rem)}
@media (max-width:767px){
    .kd-chart .card-header{flex-wrap:wrap}
    .kd-chart .card-header small{margin-left:0;width:100%}
    .kd-kpi .card-body{gap:.75rem!important}
    .kd-ico{width:42px;height:42px;font-size:1.15rem}
    .kd-bitem{flex:1 1 25%;min-width:72px}
}
@media (max-width:400px){
    .kd-bitem{flex:1 1 50%}
    .kd-bval{font-size:1rem}
}
</style>
@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Overview') }}</li>
@endsection

@section('content')
<div class="kd">

{{-- ── Banner ── --}}
<div class="kd-banner d-flex flex-wrap align-items-center justify-content-around mb-4">
    @foreach([
        ['val'=>count($agents),    'lbl'=>'Agents'],
        ['val'=>count($clients),   'lbl'=>'Clients'],
        ['val'=>count($vendors),   'lbl'=>'Vendors'],
        ['val'=>count($countries), 'lbl'=>'Countries'],
        ['val'=>$fmt($netPaid),    'lbl'=>'Collected'],
        ['val'=>$fmt($ctotdue),    'lbl'=>'Due'],
        ['val'=>$fmt($totExp),     'lbl'=>'Expenses'],
        ['val'=>$fmt($revenue),    'lbl'=>'Revenue'],
    ] as $i => $s)
        @if($i > 0)<div class="kd-bdiv d-none d-lg-block"></div>@endif
        <div class="kd-bitem"><div class="kd-bval">{{ $s['val'] }}</div><div class="kd-blbl">{{ $s['lbl'] }}</div></div>
    @endforeach
</div>

{{-- ── KPI Cards ── --}}
@php
$kpis = [
    ['lbl'=>'Total Agents',   'val'=>count($agents),    'ico'=>'ti ti-users',          'bg'=>'gi','sub'=>'▲ Registered agents',   'cls'=>'kd-up'],
    ['lbl'=>'Total Clients',  'val'=>count($clients),   'ico'=>'ti ti-user-check',     'bg'=>'ge','sub'=>'▲ Active clients',       'cls'=>'kd-up'],
    ['lbl'=>'Total Vendors',  'val'=>count($vendors),   'ico'=>'ti ti-building-store', 'bg'=>'ga','sub'=>'● Onboarded vendors',    'cls'=>'kd-nt'],
    ['lbl'=>'Countries',      'val'=>count($countries), 'ico'=>'ti ti-flag',           'bg'=>'gs','sub'=>'▲ Markets covered',      'cls'=>'kd-up'],
    ['lbl'=>'Collected',      'val'=>$fmt($netPaid),    'ico'=>'ti ti-circle-check',   'bg'=>'gt','sub'=>'▲ Total received',       'cls'=>'kd-up'],
    ['lbl'=>'Outstanding',    'val'=>$fmt($ctotdue),    'ico'=>'ti ti-clock-hour-4',   'bg'=>'gr','sub'=>'▼ Pending receivables',  'cls'=>'kd-dn'],
    ['lbl'=>'Expenses',       'val'=>$fmt($totExp),     'ico'=>'ti ti-receipt',        'bg'=>'go','sub'=>'▼ Total expenditure',    'cls'=>'kd-dn'],
    ['lbl'=>'Net Revenue',    'val'=>$fmt($revenue),    'ico'=>'ti ti-trending-up',    'bg'=>'gv','sub'=>'▲ After expenses',       'cls'=>'kd-up'],
];
@endphp
<div class="row g-3 mb-4">
    @foreach($kpis as $k)
    <div class="col-xl-3 col-md-4 col-sm-6 col-12">
        <div class="card kd-kpi h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kd-ico {{ $k['bg'] }}"><i class="{{ $k['ico'] }}"></i></div>
                <div>
                    <div class="kd-lbl">{{ $k['lbl'] }}</div>
                    <div class="kd-val">{{ $k['val'] }}</div>
                    <div class="kd-sub {{ $k['cls'] }}">{{ $k['sub'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Charts Row 1 ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card kd-chart h-100">
            <div class="card-header">
                <div class="kd-hico gi"><i class="ti ti-chart-line"></i></div>
                <h6>Monthly Payment Trend</h6>
                <small>Paid vs Outstanding</small>
            </div>
            <div class="card-body pt-2"><div id="kd-trend" style="min-height:280px"></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card kd-chart h-100">
            <div class="card-header">
                <div class="kd-hico gv"><i class="ti ti-chart-donut-3"></i></div>
                <h6>Visa Distribution</h6>
                <small>By type</small>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center pt-2">
                <div id="kd-donut" style="min-height:260px;width:100%"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row 2 ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card kd-chart h-100">
            <div class="card-header">
                <div class="kd-hico gt"><i class="ti ti-chart-bar"></i></div>
                <h6>Payment by Category</h6>
                <small>Agent · Vendor · Client</small>
            </div>
            <div class="card-body pt-2"><div id="kd-bar" style="min-height:260px"></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card kd-chart h-100">
            <div class="card-header">
                <div class="kd-hico go"><i class="ti ti-chart-area"></i></div>
                <h6>Expense Trend</h6>
                <small>Monthly expenditure</small>
            </div>
            <div class="card-body pt-2"><div id="kd-exp" style="min-height:260px"></div></div>
        </div>
    </div>
</div>


</div>{{-- .kd --}}
@endsection

@push('script-page')
<script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var grid = { borderColor: '#f1f5f9', strokeDashArray: 4 };
    var font = 'Inter, sans-serif';
    var yFmt = function(v){ return '৳' + Number(v).toLocaleString(); };

    // 1. Monthly Trend
    new ApexCharts(document.querySelector('#kd-trend'), {
        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: font, zoom: { enabled: false } },
        series: [
            { name: 'Paid', data: {!! json_encode(count($paidData) ? $paidData : [0]) !!} },
            { name: 'Due',  data: {!! json_encode(count($dueData)  ? $dueData  : [0]) !!} }
        ],
        xaxis: { categories: {!! json_encode(count($paidMonths) ? $paidMonths : ['No data']) !!}, labels: { style: { fontSize: '11px', colors: '#94a3b8' } } },
        yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: yFmt } },
        colors: ['#4f46e5', '#fb7185'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .3, opacityTo: .02 } },
        stroke: { curve: 'smooth', width: 2.5 },
        dataLabels: { enabled: false },
        grid: grid,
        legend: { position: 'top', fontFamily: font, fontSize: '12px' },
        tooltip: { y: { formatter: yFmt } }
    }).render();

    // 2. Donut
    new ApexCharts(document.querySelector('#kd-donut'), {
        chart: { type: 'donut', height: 260, fontFamily: font },
        series: {!! json_encode(count($visaCounts) ? $visaCounts : [1]) !!},
        labels: {!! json_encode(count($visaLabels) ? $visaLabels : ['No data']) !!},
        colors: ['#4f46e5', '#059669', '#d97706', '#e11d48', '#0284c7'],
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 700, color: '#0f172a' } } } } },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontFamily: font, fontSize: '12px' },
        stroke: { width: 0 }
    }).render();

    // 3. Bar
    new ApexCharts(document.querySelector('#kd-bar'), {
        chart: { type: 'bar', height: 260, stacked: true, toolbar: { show: false }, fontFamily: font },
        series: [
            { name: 'Paid', data: [{{ $totPaidA }}, {{ $totPaidV }}, {{ $totPaidC }}] },
            { name: 'Due',  data: [{{ $totDueA }},  {{ $totDueV }},  {{ $totDueC }}] }
        ],
        xaxis: { categories: ['Agents', 'Vendors', 'Clients'], labels: { style: { fontSize: '12px', colors: '#94a3b8' } } },
        yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: yFmt } },
        colors: ['#4f46e5', '#fb7185'],
        plotOptions: { bar: { borderRadius: 5, columnWidth: '42%' } },
        dataLabels: { enabled: false },
        grid: grid,
        legend: { position: 'top', fontFamily: font, fontSize: '12px' },
        tooltip: { y: { formatter: yFmt } }
    }).render();

    // 4. Expense Area
    new ApexCharts(document.querySelector('#kd-exp'), {
        chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: font, zoom: { enabled: false } },
        series: [{ name: 'Expense', data: {!! json_encode(count($expData) ? $expData : [0]) !!} }],
        xaxis: { categories: {!! json_encode(count($expMonths) ? $expMonths : ['No data']) !!}, labels: { style: { fontSize: '11px', colors: '#94a3b8' } } },
        yaxis: { labels: { style: { colors: '#94a3b8' }, formatter: yFmt } },
        colors: ['#ea580c'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .35, opacityTo: .02 } },
        stroke: { curve: 'smooth', width: 2.5 },
        dataLabels: { enabled: false },
        grid: grid,
        tooltip: { y: { formatter: yFmt } }
    }).render();
});
</script>
@endpush
