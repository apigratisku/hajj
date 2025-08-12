<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .container {
            max-width: 302px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px 10px;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 200px;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .footer .signature {
            margin-top: 60px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
        .btn-print {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <a href="<?= base_url('database') ?>" class="btn-back">Kembali</a>
        <button onclick="window.print()" class="btn-print">Cetak Laporan</button>
    </div>
    
    <div class="container">
        <div class="header">
            <h1><?php echo '<img src="'.base_url('assets/img/ASDP_Logo2.png').'" alt="Logo" style="width: 100px;">' ?></h1>
            <p><span style="font-size: 12px; font-weight: bold;">Struk Transaksi</span></p>
        </div>
        
        <table class="info-table">
            <tr>
                <td style="width: 45%;">Kode Trx</td>
                <td>: <?= $transaksi->kode_transaksi ?></td>
            </tr>
            <tr>
                <td>Nama Kapal</td>
                <td>: <?= $transaksi->nama_kapal ?></td>
            </tr>
            <tr>
                <td>Perusahaan</td>
                <td>: <?= $transaksi->perusahaan ?></td>
            </tr>
            <tr>
                <td>Dermaga</td>
                <td>: Dermaga <?= $transaksi->dermaga ?></td>
            </tr>
            <tr>
                <td>Mulai Sandar</td>
                <td>: <?= $transaksi->waktu_mulai_sandar ? date('d/m/Y H:i:s', strtotime($transaksi->waktu_mulai_sandar)) : '-' ?></td>
            </tr>
            <tr>
                <td>Selesai Sandar</td>
                <td>: <?= $transaksi->waktu_selesai_sandar ? date('d/m/Y H:i:s', strtotime($transaksi->waktu_selesai_sandar)) : '-' ?></td>
            </tr>
            <tr>
                <td>Durasi Sandar</td>
                <td>: <?= $transaksi->durasi_sandar ? $transaksi->durasi_sandar : '-' ?></td>
            </tr>
            <tr>
                <td>Volume Terisi</td>
                <td>: <?= number_format($transaksi->volume_air, 2) ?> Liter</td>
            </tr>
            
            <tr>
                <td>Status Transaksi</td>
                <td>: <?= isset($transaksi->status_trx) && $transaksi->status_trx == 1 ? 'Selesai' : 'Aktif' ?></td>
            </tr>
            <tr>
                <td>Petugas</td>
                <td>: <?= $this->session->userdata('nama_lengkap') ?></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-size: 10px; font-weight: normal;">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-size: 10px; font-weight: normal;"><p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p></td>
            </tr>
        </table>
        
       
    </div>
    
    <script>
        // Auto print when page loads (uncomment if needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // }
    </script>
</body>
</html> 