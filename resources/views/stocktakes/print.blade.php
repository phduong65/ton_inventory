<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In phiếu kiểm kê {{ $stocktake->code }}</title>
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
        .print-btn { position: fixed; top: 16px; right: 16px; padding: 8px 16px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; }
        @media print { .print-btn { display: none; } @page { margin: 15mm; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">🖨️ In</button>

    <div class="header">
        <p style="font-size:12px; font-weight:bold">{{ strtoupper($companyName ?: 'CÔNG TY F&B') }}</p>
        @if($companyAddress)<p style="font-size:11px">{{ $companyAddress }}</p>@endif
        <h1>PHIẾU KIỂM KÊ KHO</h1>
        <h2>Số: {{ $stocktake->code }}</h2>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Ngày kiểm kê:</span>
            <span>{{ $stocktake->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Phạm vi:</span>
            <span>
                @if($stocktake->destination)
                    {{ $stocktake->destination->name }}
                @elseif($stocktake->category)
                    Ngành: {{ $stocktake->category->name }}
                @else
                    Kho Tổng (toàn bộ)
                @endif
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Người tạo:</span>
            <span>{{ $stocktake->createdBy?->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Người duyệt:</span>
            <span>{{ $stocktake->approvedBy?->name ?? '—' }}</span>
        </div>
        @if($stocktake->note)
        <div class="info-row" style="grid-column:1/-1">
            <span class="info-label">Ghi chú:</span>
            <span>{{ $stocktake->note }}</span>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">STT</th>
                <th>Tên sản phẩm</th>
                <th style="width:70px">SKU</th>
                <th style="width:60px">ĐVT</th>
                <th style="width:100px">{{ $stocktake->destination ? 'Đã nhận (HT)' : 'Tồn hệ thống' }}</th>
                <th style="width:90px">Thực tế</th>
                <th style="width:90px">Chênh lệch</th>
                <th>Ghi chú</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocktake->details as $i => $detail)
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>{{ $detail->product?->name }}</td>
                <td class="center">{{ $detail->product?->sku ?? '—' }}</td>
                <td class="center">{{ $detail->product?->unit?->name ?? '—' }}</td>
                <td class="right">{{ number_format($detail->system_qty, 0, ',', '.') }}</td>
                <td class="right">{{ number_format($detail->actual_qty, 0, ',', '.') }}</td>
                <td class="right" style="{{ $detail->variance > 0 ? 'color:#16a34a' : ($detail->variance < 0 ? 'color:#dc2626' : '') }}">
                    {{ $detail->variance >= 0 ? '+' : '' }}{{ number_format($detail->variance, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right">Tổng cộng</td>
                <td class="right">{{ number_format($stocktake->details->sum('system_qty'), 0, ',', '.') }}</td>
                <td class="right">{{ number_format($stocktake->details->sum('actual_qty'), 0, ',', '.') }}</td>
                <td class="right">
                    @php $totalVariance = $stocktake->details->sum('variance'); @endphp
                    {{ $totalVariance >= 0 ? '+' : '' }}{{ number_format($totalVariance, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <div>
            <p class="sig-title">Người kiểm kê</p>
            <p>{{ $stocktake->createdBy?->name }}</p>
        </div>
        <div>
            <p class="sig-title">Thủ kho</p>
            <p>&nbsp;</p>
        </div>
        <div>
            <p class="sig-title">Người duyệt</p>
            <p>{{ $stocktake->approvedBy?->name ?? '&nbsp;' }}</p>
        </div>
    </div>
</body>
</html>
