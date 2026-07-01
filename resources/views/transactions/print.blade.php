<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In phiếu {{ $transaction->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { background: #d1d5db; }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 13px;
            line-height: 1.6;
            color: #000;
        }

        /* ── A4 wrapper ── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 24px auto;
            padding: 18mm 20mm 24mm;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.25);
        }

        .print-btn-bar {
            text-align: center;
            padding: 12px 0 20px;
        }
        .print-btn-bar button {
            padding: 8px 28px;
            font-size: 14px;
            cursor: pointer;
            border: 1px solid #555;
            border-radius: 4px;
            background: #fff;
        }

        /* ── Document header ── */
        .doc-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
        }
        .company-block { font-size: 12px; line-height: 1.7; }
        .company-block .company-name { font-size: 13px; font-weight: bold; }

        .doc-title-block {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .doc-title-block h1 {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title-block .doc-code {
            font-size: 13px;
            margin-top: 4px;
        }
        .doc-title-block .doc-status {
            display: inline-block;
            margin-top: 6px;
            font-size: 11px;
            padding: 1px 8px;
            border: 1px solid #555;
            border-radius: 3px;
        }

        hr.divider {
            border: none;
            border-top: 1px solid #000;
            margin: 10px 0 14px;
        }

        /* ── Info section ── */
        .info-section {
            margin-bottom: 18px;
            font-size: 13px;
        }
        .info-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 32px;
        }
        .info-row {
            display: flex;
            gap: 6px;
            min-height: 22px;
            align-items: baseline;
        }
        .info-row.full { grid-column: 1 / -1; }
        .lbl {
            font-weight: bold;
            white-space: nowrap;
            min-width: 100px;
        }
        .val { border-bottom: 1px dotted #666; flex: 1; }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 7px 9px;
            line-height: 1.45;
        }
        th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 12.5px;
        }
        td.center { text-align: center; }
        td.right   { text-align: right; }
        tfoot td   { font-weight: bold; }
        tfoot tr.total-row td { background: #f8f8f8; }

        /* ── Amount in words ── */
        .amount-words {
            font-size: 13px;
            margin: 8px 0 16px;
        }
        .amount-words .lbl { font-weight: bold; }

        /* ── Signatures ── */
        .signatures {
            display: grid;
            gap: 16px;
            margin-top: 36px;
            text-align: center;
        }
        .sig-col .sig-title { font-weight: bold; margin-bottom: 52px; font-size: 13px; }
        .sig-col .sig-name  { font-size: 12.5px; }

        /* ── Footer note ── */
        .print-note {
            margin-top: 20px;
            font-size: 11px;
            color: #555;
            text-align: right;
        }

        /* ── Attachments ── */
        .attachments { margin-top: 16px; }
        .attachments .section-title { font-weight: bold; margin-bottom: 8px; }
        .img-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .img-grid img {
            width: 160px; height: 120px;
            object-fit: cover;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* ── Print ── */
        @media print {
            @page { size: A4; margin: 18mm 20mm; }
            html, body { background: #fff; }
            .page {
                width: auto; min-height: auto;
                margin: 0; padding: 0;
                box-shadow: none;
            }
            .print-btn-bar { display: none; }
            .img-grid img { width: 140px; height: 105px; page-break-inside: avoid; }
        }
    </style>
</head>

<body>
@php
    $companyName    = \App\Models\Setting::get('company_name', 'CÔNG TY F&B');
    $companyAddress = \App\Models\Setting::get('company_address', '');
    $companyPhone   = \App\Models\Setting::get('company_phone', '');
    $companyTaxCode = \App\Models\Setting::get('company_tax_code', '');
    $warehouseName  = \App\Models\Setting::get('warehouse_name', 'Kho Tổng');

    $isIN  = $transaction->type === 'IN';
    $isOUT = $transaction->type === 'OUT';

    $statusLabel = match($transaction->status) {
        'approved' => 'ĐÃ DUYỆT',
        'pending'  => 'CHỜ DUYỆT',
        'rejected' => 'TỪ CHỐI',
        default    => 'NHÁP',
    };

    // Total quantity
    $totalQty = $transaction->details->sum('qty');

    // Vietnamese number to words
    function soTienBangChu(int $so): string {
        if ($so === 0) return 'Không đồng';
        $donvi = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

        $docNhom = function(int $n) use ($donvi): string {
            if ($n === 0) return '';
            if ($n < 10) return $donvi[$n];
            if ($n < 20) {
                $u = $n % 10;
                return 'mười' . ($u > 0 ? ' ' . ($u === 5 ? 'lăm' : $donvi[$u]) : '');
            }
            $h = intdiv($n, 10); $u = $n % 10;
            $r = $donvi[$h] . ' mươi';
            if ($u === 1)      $r .= ' mốt';
            elseif ($u === 5)  $r .= ' lăm';
            elseif ($u > 0)    $r .= ' ' . $donvi[$u];
            return $r;
        };

        $docBaChu = function(int $n, bool $first = false) use ($donvi, $docNhom): string {
            $tr = intdiv($n, 100); $r = $n % 100;
            $res = '';
            if ($tr > 0) $res = $donvi[$tr] . ' trăm';
            elseif (!$first) $res = 'không trăm';
            if ($r > 0) {
                if ($tr > 0 || !$first) $res .= ($r < 10 ? ' linh' : '');
                $res .= ' ' . $docNhom($r);
            }
            return trim($res);
        };

        $ty = intdiv($so, 1_000_000_000); $r = $so % 1_000_000_000;
        $tr = intdiv($r,  1_000_000);     $r = $r  % 1_000_000;
        $ng = intdiv($r,  1_000);         $dv = $r % 1_000;

        $parts = [];
        if ($ty > 0) $parts[] = $docBaChu($ty, true)         . ' tỷ';
        if ($tr > 0) $parts[] = $docBaChu($tr, empty($parts)) . ' triệu';
        if ($ng > 0) $parts[] = $docBaChu($ng, empty($parts)) . ' nghìn';
        if ($dv > 0) $parts[] = $docBaChu($dv, empty($parts));

        return ucfirst(implode(' ', $parts)) . ' đồng';
    }

    $totalAmount = (int) $transaction->details->sum('amount');
@endphp

<div class="print-btn-bar">
    <button onclick="window.print()">&#128438; In phiếu</button>
</div>

<div class="page">

    {{-- ═══ TIÊU ĐỀ ═══ --}}
    <div class="doc-header">
        <div class="company-block">
            <div class="company-name">{{ strtoupper($companyName ?: 'CÔNG TY F&B') }}</div>
            @if ($companyAddress)
                <div>Địa chỉ: {{ $companyAddress }}</div>
            @endif
            @if ($companyPhone)
                <div>Điện thoại: {{ $companyPhone }}</div>
            @endif
            @if ($companyTaxCode)
                <div>MST: {{ $companyTaxCode }}</div>
            @endif
        </div>
        <div class="doc-title-block">
            <h1>{{ $isIN ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }}</h1>
            <div class="doc-code">Số: <strong>{{ $transaction->code }}</strong></div>
            <div>
                <span class="doc-status">{{ $statusLabel }}</span>
            </div>
        </div>
    </div>

    <hr class="divider">

    {{-- ═══ THÔNG TIN PHIẾU ═══ --}}
    <div class="info-section">
        <div class="info-grid-2">

            <div class="info-row">
                <span class="lbl">Ngày:</span>
                <span class="val">{{ $transaction->date?->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">{{ $isIN ? 'Nhập vào kho:' : 'Xuất từ kho:' }}</span>
                <span class="val">{{ $warehouseName }}</span>
            </div>

            <div class="info-row">
                <span class="lbl">{{ $isIN ? 'Nhà cung cấp:' : 'Điểm nhận:' }}</span>
                <span class="val">{{ $isIN ? ($transaction->supplier?->name ?? '—') : ($transaction->destination?->name ?? '—') }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">{{ $isIN ? 'Người giao:' : 'Người nhận:' }}</span>
                <span class="val">{{ $isIN ? ($transaction->supplier?->contact_person ?? '—') : ($transaction->destination?->manager ?? '—') }}</span>
            </div>

            <div class="info-row">
                <span class="lbl">Người lập:</span>
                <span class="val">{{ $transaction->createdBy?->name }}</span>
            </div>
            <div class="info-row">
                <span class="lbl">Người duyệt:</span>
                <span class="val">{{ $transaction->approvedBy?->name ?? '—' }}</span>
            </div>

            @if ($transaction->note)
                <div class="info-row full">
                    <span class="lbl">{{ $isOUT ? 'Lý do xuất:' : 'Ghi chú:' }}</span>
                    <span class="val">{{ $transaction->note }}</span>
                </div>
            @endif

        </div>
    </div>

    {{-- ═══ BẢNG HÀNG HÓA ═══ --}}
    <table>
        <thead>
            <tr>
                <th style="width:36px">STT</th>
                <th>Tên hàng hóa</th>
                <th style="width:54px">ĐVT</th>
                <th style="width:72px">Số lượng</th>
                @if ($isIN)
                    <th style="width:95px">Đơn giá</th>
                    <th style="width:52px">CK%</th>
                    <th style="width:52px">VAT%</th>
                    <th style="width:110px">Thành tiền</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->details as $i => $detail)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $detail->product?->name }}</td>
                    <td class="center">{{ $detail->product?->unit?->name ?? '—' }}</td>
                    <td class="right">{{ number_format($detail->qty, 0, ',', '.') }}</td>
                    @if ($isIN)
                        <td class="right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                        <td class="center">{{ $detail->discount }}%</td>
                        <td class="center">{{ $detail->vat }}%</td>
                        <td class="right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                @if ($isIN)
                    <td colspan="3" class="right">Tổng cộng:</td>
                    <td class="right">{{ number_format($totalQty, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                    <td class="right">{{ number_format($totalAmount, 0, ',', '.') }}đ</td>
                @else
                    <td colspan="3" class="right">Tổng số lượng:</td>
                    <td class="right">{{ number_format($totalQty, 0, ',', '.') }}</td>
                @endif
            </tr>
        </tfoot>
    </table>

    {{-- Số tiền bằng chữ (chỉ phiếu nhập) --}}
    @if ($isIN)
        <div class="amount-words">
            <span class="lbl">Số tiền bằng chữ:</span>
            {{ soTienBangChu($totalAmount) }}
        </div>
    @endif

    {{-- ═══ HÌNH ẢNH ═══ --}}
    @php $images = $transaction->attachments->filter(fn($a) => $a->isImage()); @endphp
    @if ($isIN && $images->isNotEmpty())
        <div class="attachments">
            <p class="section-title">Hình ảnh đính kèm ({{ $images->count() }} ảnh):</p>
            <div class="img-grid">
                @foreach ($images as $att)
                    <img src="{{ $att->url }}" alt="{{ $att->original_name }}" title="{{ $att->original_name }}">
                @endforeach
            </div>
        </div>
    @endif

    {{-- ═══ KÝ TÊN ═══ --}}
    @if ($isIN)
        {{-- 4 cột: Người lập | Thủ kho | Người giao hàng | Kế toán --}}
        <div class="signatures" style="grid-template-columns: 1fr 1fr 1fr 1fr;">
            <div class="sig-col">
                <div class="sig-title">Người lập phiếu</div>
                <div class="sig-name">{{ $transaction->createdBy?->name }}</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Thủ kho</div>
                <div class="sig-name">&nbsp;</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Người giao hàng</div>
                <div class="sig-name">{{ $transaction->supplier?->contact_person }}</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Người duyệt</div>
                <div class="sig-name">{{ $transaction->approvedBy?->name }}</div>
            </div>
        </div>
    @else
        {{-- 4 cột: Người lập | Thủ kho | Người nhận hàng | Người duyệt --}}
        <div class="signatures" style="grid-template-columns: 1fr 1fr 1fr 1fr;">
            <div class="sig-col">
                <div class="sig-title">Người lập phiếu</div>
                <div class="sig-name">{{ $transaction->createdBy?->name }}</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Thủ kho</div>
                <div class="sig-name">&nbsp;</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Người nhận hàng</div>
                <div class="sig-name">{{ $transaction->destination?->manager }}</div>
            </div>
            <div class="sig-col">
                <div class="sig-title">Người duyệt</div>
                <div class="sig-name">{{ $transaction->approvedBy?->name }}</div>
            </div>
        </div>
    @endif

    <div class="print-note">
        In lúc: {{ now()->format('H:i d/m/Y') }}
    </div>

</div>
</body>

</html>
