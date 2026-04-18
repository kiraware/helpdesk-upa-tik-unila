<!DOCTYPE html>
<html>

<head>
    <title>Surat Tugas - {{ $ticket->ticket_code }}</title>
    <style>
        /* MENGUBAH UKURAN KERTAS KE F4 (FOLIO) */
        @page {
            size: 21.5cm 33cm;
            margin: 2cm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
        }

        /* --- CSS KOP SURAT --- */
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            table-layout: fixed;
        }

        .header-text {
            text-align: center;
        }

        .header-text h3 {
            font-size: 14pt;
            font-weight: normal;
            margin: 0;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 14pt;
            font-weight: normal;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .header-text h4 {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header-text p {
            font-size: 11pt;
            margin: 2px 0 0 0;
        }

        .title-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .surat-tugas {
            font-weight: bold;
            text-decoration: underline;
            font-size: 14pt;
            text-transform: uppercase;
            margin: 0;
        }

        .nomor-surat {
            margin-top: 5px;
            font-size: 12pt;
        }

        /* --- TABEL IDENTITAS --- */
        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label-col {
            width: 2%;
            white-space: nowrap;
        }

        .separator-col {
            width: 2%;
            white-space: nowrap;
            padding: 0 10px;
            text-align: center;
        }

        .value-col {
            max-width: 550px;
            word-wrap: break-word;
            word-break: break-all;
        }

        /* --- TABEL DETAIL TIKET --- */
        .bordered-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 10px;
            table-layout: fixed;
        }

        .bordered-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
            word-wrap: break-word;
            word-break: break-all;
        }

        .bg-gray {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 25%;
        }

        .justify {
            text-align: justify;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        /* --- CONTAINER TTD --- */
        .signature-container {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            table-layout: fixed;
        }

        /* --- STYLING TABEL TANDA TANGAN BAWAH (BORDERED) --- */
        .bottom-signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 60px;
            /* Jarak aman ke TTD atas */
            table-layout: fixed;
        }

        .bottom-signature-table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td width="15%" style="vertical-align: middle;">
                <img src="{{ public_path('img/logo-unila.png') }}" style="width: 85px; height: auto;">
            </td>
            <td width="85%" align="center">
                <div class="header-text">
                    <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
                    <h2>UNIVERSITAS LAMPUNG</h2>
                    <h4>UPA TEKNOLOGI INFORMASI DAN KOMUNIKASI</h4>
                    <p>Jalan Prof. Dr. Sumantri Brojonegoro No. 1 Bandar Lampung 35145</p>
                    <p>Email: tik@kpa.unila.ac.id | Website: https://tik.unila.ac.id</p>
                </div>
            </td>
        </tr>
    </table>

    <div class="title-container">
        <h1 class="surat-tugas">SURAT TUGAS</h1>
        <p class="nomor-surat">Nomor: _______________/UN26.32/TI/{{ date('Y') }}</p>
    </div>

    <div class="justify">
        <p>Berdasarkan Tiket <strong>#{{ $ticket->ticket_code }}</strong> Helpdesk UPA TIK Universitas Lampung. Kepala
            UPA TIK Universitas Lampung menugaskan nama di bawah ini:</p>
    </div>

    {{-- TABEL IDENTITAS --}}
    <table class="content-table">
        <tr>
            <td class="label-col">Nama</td>
            <td class="separator-col">:</td>
            <td class="value-col">{{ $ticket->assignee->name }}</td>
        </tr>
        <tr>
            <td class="label-col">NIP/NIK</td>
            <td class="separator-col">:</td>
            <td class="value-col">{{ $ticket->assignee->identity_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label-col">Divisi</td>
            <td class="separator-col">:</td>
            <td class="value-col">{{ $ticket->assignee->division->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="justify">
        <p>Untuk menyelesaikan permasalahan dengan detil tiket:</p>
    </div>

    {{-- TABEL BORDERED --}}
    <table class="bordered-table">
        <tr>
            <td class="bg-gray">Kode Tiket</td>
            <td>
                #{{ $ticket->ticket_code }} ({{ $ticket->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB)
            </td>
        </tr>
        <tr>
            <td class="bg-gray">Layanan</td>
            <td>{{ $ticket->service->name }} - {{ ucfirst($ticket->priority->value) }}</td>
        </tr>

        <tr>
            <td class="bg-gray">Pelapor</td>
            <td>
                @if ($ticket->user_id)
                    {{-- Jika USER (Internal/Civitas) --}}
                    {{ $ticket->user->name }}
                    | {{ $ticket->user->identity_number ?? '-' }}
                    | {{ ucfirst($ticket->user->role->value) }}
                @elseif($ticket->guestDetail)
                    {{-- Jika GUEST (Tamu) --}}
                    {{ $ticket->guestDetail->full_name }}
                    | {{ $ticket->guestDetail->identity_number ?? '-' }}
                    | {{ ucfirst($ticket->guestDetail->entity_type->value) }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="justify">
        <p>Demikian surat tugas ini dibuat, agar dapat dilaksanakan dengan sebaik-baiknya.</p>
    </div>

    <div class="signature-container">

        {{-- TTD KEPALA UPA (ATAS KANAN - TANPA BORDER) --}}
        <table class="signature-table">
            <tr>
                <td width="55%"></td>
                <td width="45%" align="left">
                    <p style="margin-bottom: 5px;">
                        Bandar Lampung, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}
                    </p>
                    <p style="margin: 0; font-weight: bold;">{{ $kepalaUpa['jabatan'] }}</p>

                    <div style="height: 85px;"></div>

                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        {{ $kepalaUpa['name'] }}
                    </p>
                    <p style="margin: 0; font-weight: bold;">NIP. {{ $kepalaUpa['nip'] }}</p>
                </td>
            </tr>
        </table>

        {{-- TABEL TTD BAWAH (DENGAN BORDER) --}}
        <table class="bottom-signature-table">
            {{-- Baris 1: Judul --}}
            <tr>
                <td width="33%" align="center" style="font-weight: bold; background-color: #f0f0f0;">TTD Petugas</td>
                <td width="34%" align="center" style="font-weight: bold; background-color: #f0f0f0;">TTD User</td>
                <td width="33%" align="center" style="font-weight: bold; background-color: #f0f0f0;">TTD Penanggung
                    Jawab</td>
            </tr>

            {{-- Baris 2: Space Tanda Tangan --}}
            <tr>
                <td align="center" style="height: 70px; vertical-align: bottom;">
                    ( ........................................... )
                </td>
                <td align="center" style="height: 70px; vertical-align: bottom;">
                    ( ........................................... )
                </td>
                <td align="center" style="height: 70px; vertical-align: bottom;">
                    ( ........................................... )
                </td>
            </tr>
        </table>

    </div>

</body>

</html>
