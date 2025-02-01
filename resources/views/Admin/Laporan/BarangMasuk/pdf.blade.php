<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        .header {
            text-align: left;
            margin-bottom: 20px;
        }

        .logo {
            max-width: 150px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            margin-top: 50px;
            width: 100%;
        }

        .signatures {
            width: 100%;
            display: table;
            margin-top: 50px;
        }

        .signature-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }

        .signature-line {
            margin-top: 50px;
            border-bottom: 1px solid black;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-cell img {
        max-width: 150px;
        margin: 10px auto;
        display: block;
    }
    
    .signature-cell {
        vertical-align: top;
        padding: 15px;
        border: none;
    }
    
    .signatures {
        page-break-inside: avoid;
        margin-top: 30px;
    }
    <style>
    .signature-cell img {
        max-width: 150px;
        max-height: 100px;
        object-fit: contain;
        margin: 10px auto;
        display: block;
    }
    
    .signature-cell {
        vertical-align: top;
        padding: 15px;
        border: none;
    }
    
    .signatures {
        page-break-inside: avoid;
        margin-top: 30px;
    }
    
    .signature-line {
        margin-top: 10px;
        border-bottom: 1px solid black;
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }
</style>
    </style>
</head>

<body>
    <div class="header" style="position: relative;">
        <!-- Add logo -->
        <div style="position: absolute; top: 0; right: 0;">
            <img src="{{ public_path('assets/images/logoug.png') }}" style="width: 100px; height: auto;">
        </div>
        <h2 style="margin-bottom: 8px;">PT. USAHA GEDUNG MANDIRI</h2>
        <p style="margin: 4px 0;">WISMA MANDIRI Lantai XII</p>
        <p style="margin: 4px 0;">Jl. M.H Tamrin no. 5</p>
        <p style="margin: 4px 0;">Jakarta 10340</p>
        <p style="margin: 4px 0;">Phone: (021) 2300 8000, 390 2020</p>
        <p style="margin: 4px 0;">Fax: (0210 230 2752)</p>
    </div>

    <h1 style="text-align: center;">Tanda Terima</h1>
    <div style="right: 20px; font-weight: bold;">
        Request ID: {{ $request->request_id }}
    </div>
    <p>Tanggal: {{ Carbon\Carbon::parse($request->request_tanggal)->translatedFormat('d F Y') }}</p>
    <p>Divisi: {{ $request->divisi }}</p>
    <p>Departemen: {{ $request->departemen }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Jumlah Barang</th>
                <th>Harga</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($data as $key => $d)
                @php $total += $d->harga * $d->bm_jumlah; @endphp
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $d->barang_kode }}</td>
                    <td>{{ $d->barang_nama }}</td>
                    <td>{{ $d->bm_jumlah }}</td>
                    <td>Rp {{ number_format($d->harga, 0, ',', '.') }}</td>
                    <td>{{ $d->tracking_status }}</td>
                    <td>{{ $d->keterangan }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" align="right"><strong>Total</strong></td>
                <td colspan="3"><strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

<div class="signatures">
    <div class="signature-cell">
        <p>Disetujui oleh,</p>
        @if(isset($signatures['GM']) && !empty($signatures['GM']->signature_base64))
            <img src="{{ $signatures['GM']->signature_base64 }}" alt="Tanda Tangan GM"
                style="max-width: 150px; max-height: 100px; object-fit: contain;">
        @endif
        <div class="signature-line"></div>
        <p>{{ isset($signatures['GM']) ? $signatures['GM']->user_nmlengkap : '________________' }}</p>
    </div>

    <div class="signature-cell">
        <p>Penerima Barang,</p>
        @if(isset($signatures['User']) && !empty($signatures['User']->signature_base64))
            <img src="{{ $signatures['User']->signature_base64 }}" alt="Tanda Tangan Penerima"
                style="max-width: 150px; max-height: 100px; object-fit: contain;">
        @endif
        <div class="signature-line"></div>
        <p>{{ isset($signatures['User']) ? $signatures['User']->user_nmlengkap : '________________' }}</p>
    </div>

    <div class="signature-cell">
        <p>Permohonan Barang,</p>
        @if(isset($signatures['GMHCGA']) && !empty($signatures['GMHCGA']->signature_base64))
            <img src="{{ $signatures['GMHCGA']->signature_base64 }}" alt="Tanda Tangan GMHCGA"
                style="max-width: 150px; max-height: 100px; object-fit: contain;">
        @endif
        <div class="signature-line"></div>
        <p>{{ isset($signatures['GMHCGA']) ? $signatures['GMHCGA']->user_nmlengkap : '________________' }}</p>
    </div>
</div>
</body>

</html>