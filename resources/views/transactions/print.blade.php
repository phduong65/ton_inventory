<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In phiếu {{ $transaction->code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', serif; font-size: 13px; color: #000; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin-top: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 16px; font-size: 13px; }
        .info-row { display: flex; gap: 8px; }
        .info-label { font-weight: bold; white-space: nowrap; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #000; padding: 5px 8px; }
        th { background: #f0f0f0; font-weight: bold; text-align: center; }
        td.center { text-align: center; }
        td.right { text-align: right; }
        tfoot td { font-weight: bold; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 32px; text-align: center; }
        .sig-title { font-weight: bold; margin-bottom: 48px; }
        .attachments { margin-top: 20px; }
        .attachments .section-title { font-weight: bold; margin-bottom: 10px; font-size: 13px; }
        .img-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .img-grid img { width: 160px; height: 120px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px; }
        @media print {
            @page { margin: 15mm; }
            button { display: none; }
            .img-grid img { width: 140px; height: 105px; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <p style="font-size:12px">CÔNG TY F&B — KHO TỔNG (KHO 40)</p>
        <h1>{{ $transaction->type === 'IN' ? 'PHIẾU NHẬP KHO' : 'PHIẾU XUẤT KHO' }}</h1>
        <h2>Số: {{ $transaction->code }}</h2>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Ngày:</span>
            <span>{{ $transaction->date?->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ $transaction->type === 'IN' ? 'Nhà cung cấp:' : 'Điểm nhận:' }}</span>
            <span>{{ $transaction->supplier?->name ?? $transaction->destination?->name ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Người tạo:</span>
            <span>{{ $transaction->createdBy?->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Người duyệt:</span>
            <span>{{ $transaction->approvedBy?->name ?? '—' }}</span>
        </div>
        @if($transaction->note)
        <div class="info-row" style="grid-column:1/-1">
            <span class="info-label">Ghi chú:</span>
            <span>{{ $transaction->note }}</span>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:30px">STT</th>
                <th>Tên hàng hóa</th>
                <th style="width:50px">ĐVT</th>
                <th style="width:60px">Số lượng</th>
                @if($transaction->type === 'IN')
                <th style="width:90px">Đơn giá</th>
                <th style="width:50px">CK%</th>
                <th style="width:50px">VAT%</th>
                <th style="width:100px">Thành tiền</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $i => $detail)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $detail->product?->name }}</td>
                <td class="center">{{ $detail->product?->unit?->name ?? '—' }}</td>
                <td class="right">{{ number_format($detail->qty, 0, ',', '.') }}</td>
                @if($transaction->type === 'IN')
                <td class="right">{{ number_format($detail->price, 0, ',', '.') }}</td>
                <td class="center">{{ $detail->discount }}%</td>
                <td class="center">{{ $detail->vat }}%</td>
                <td class="right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        @if($transaction->type === 'IN')
        <tfoot>
            <tr>
                <td colspan="7" class="right">TỔNG CỘNG:</td>
                <td class="right">{{ number_format($transaction->details->sum('amount'), 0, ',', '.') }}đ</td>
            </tr>
        </tfoot>
        @endif
    </table>

    @php $images = $transaction->attachments->filter(fn($a) => $a->isImage()); @endphp
    @if($transaction->type === 'IN' && $images->isNotEmpty())
    <div class="attachments">
        <p class="section-title">Hình ảnh đính kèm ({{ $images->count() }} ảnh):</p>
        <div class="img-grid">
            @foreach($images as $att)
            <img src="{{ $att->url }}" alt="{{ $att->original_name }}" title="{{ $att->original_name }}">
            @endforeach
        </div>
    </div>
    @endif

    <div class="signatures">
        <div>
            <div class="sig-title">Người lập phiếu</div>
            <p>{{ $transaction->createdBy?->name }}</p>
        </div>
        <div>
            <div class="sig-title">Thủ kho</div>
            <p>&nbsp;</p>
        </div>
        <div>
            <div class="sig-title">Người duyệt</div>
            <p>{{ $transaction->approvedBy?->name }}</p>
        </div>
    </div>

    <div style="text-align:center; margin-top:24px">
        <button onclick="window.print()" style="padding:8px 20px; cursor:pointer;">In phiếu</button>
    </div>
</body>
</html>
