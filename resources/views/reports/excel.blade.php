{{--
    Fallback Excel view — used only if the FromView approach is preferred
    over WithMultipleSheets for simpler deployments.
    For the multi-sheet version, use TicketReportExport with Sheets/*.
--}}
<table>
    <tr>
        <th colspan="15"
            style="text-align: center; font-weight: bold; font-size: 16px; background-color: #1E40AF; color: #FFFFFF; padding: 10px;">
            LAPORAN HELPDESK TIK UNIVERSITAS LAMPUNG
        </th>
    </tr>
    <tr>
        <th colspan="15"
            style="text-align: center; font-weight: bold; font-size: 12px; background-color: #DBEAFE; color: #1E40AF;">
            Periode: {{ $startDate->format('d F Y') }} s.d. {{ $endDate->format('d F Y') }}
        </th>
    </tr>
    <tr>
        <td colspan="15" style="text-align: right; font-style: italic; font-size: 9px; color: #6B7280;">
            Dicetak: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB &nbsp;|&nbsp; Sistem Helpdesk TIK UNILA
        </td>
    </tr>
    <tr></tr>

    <tr>
        <th colspan="5"
            style="background-color: #059669; color: #FFFFFF; font-weight: bold; font-size: 11px; padding: 6px;">
            RINGKASAN STATUS TIKET
        </th>
    </tr>
    <tr style="background-color: #374151; color: #FFFFFF; font-weight: bold; text-align: center;">
        <th>Keterangan</th>
        <th>Jumlah</th>
        <th>Persentase</th>
        <th></th>
        <th></th>
    </tr>
    @php
        $grandTotal = 0;
        $grandDone = 0;
        $grandReject = 0;
        $grandWait = 0;
        $grandProg = 0;
        foreach ($reportData as $row) {
            $grandTotal += $row['total'];
            $grandDone += $row['done'];
            $grandReject += $row['reject'];
            $grandWait += $row['waiting'] ?? 0;
            $grandProg += $row['progress'] ?? 0;
        }
        $gt = $grandTotal ?: 1;
    @endphp
    <tr>
        <td style="font-weight: bold;">Total Tiket Masuk</td>
        <td style="text-align: center; font-weight: bold;">{{ $grandTotal }}</td>
        <td style="text-align: center;">100%</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;- Menunggu</td>
        <td style="text-align: center;">{{ $grandWait }}</td>
        <td style="text-align: center;">{{ round(($grandWait / $gt) * 100, 1) }}%</td>
        <td></td>
        <td></td>
    </tr>
    <tr style="background-color: #F9FAFB;">
        <td>&nbsp;&nbsp;- Diproses</td>
        <td style="text-align: center;">{{ $grandProg }}</td>
        <td style="text-align: center;">{{ round(($grandProg / $gt) * 100, 1) }}%</td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;&nbsp;- Selesai (Done)</td>
        <td style="text-align: center; color: #059669; font-weight: bold;">{{ $grandDone }}</td>
        <td style="text-align: center;">{{ round(($grandDone / $gt) * 100, 1) }}%</td>
        <td></td>
        <td></td>
    </tr>
    <tr style="background-color: #F9FAFB;">
        <td>&nbsp;&nbsp;- Ditolak (Reject)</td>
        <td style="text-align: center; color: #DC2626; font-weight: bold;">{{ $grandReject }}</td>
        <td style="text-align: center;">{{ round(($grandReject / $gt) * 100, 1) }}%</td>
        <td></td>
        <td></td>
    </tr>
    <tr style="font-weight: bold; background-color: #F0FDF4;">
        <td>Tingkat Penyelesaian</td>
        <td style="text-align: center; color: #059669;">{{ round(($grandDone / $gt) * 100, 1) }}%</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr></tr>

    <tr>
        <th colspan="5"
            style="background-color: #1E40AF; color: #FFFFFF; font-weight: bold; font-size: 11px; padding: 6px;">
            REKAP TIKET BERDASARKAN LAYANAN
        </th>
    </tr>
    <tr style="background-color: #374151; color: #FFFFFF; font-weight: bold; text-align: center;">
        <th>No</th>
        <th>Layanan</th>
        <th>Total</th>
        <th>Selesai</th>
        <th>Ditolak</th>
    </tr>
    @php $no = 1; @endphp
    @foreach ($reportData as $row)
        <tr style="{{ $no % 2 === 0 ? 'background-color: #F9FAFB;' : '' }}">
            <td style="text-align: center;">{{ $no++ }}</td>
            <td>{{ $row['name'] }}</td>
            <td style="text-align: center; font-weight: bold; color: #2563EB;">{{ $row['total'] }}</td>
            <td style="text-align: center; font-weight: bold; color: #059669;">{{ $row['done'] }}</td>
            <td style="text-align: center; font-weight: bold; color: #DC2626;">{{ $row['reject'] }}</td>
        </tr>
    @endforeach
    <tr style="font-weight: bold; background-color: #1E40AF; color: #FFFFFF; text-align: center;">
        <td colspan="2" style="text-align: left; padding-left: 8px;">TOTAL</td>
        <td>{{ $grandTotal }}</td>
        <td>{{ $grandDone }}</td>
        <td>{{ $grandReject }}</td>
    </tr>
    <tr></tr>

    <tr>
        <th colspan="11"
            style="background-color: #7C3AED; color: #FFFFFF; font-weight: bold; font-size: 11px; padding: 6px;">
            DISTRIBUSI TIKET PER LAYANAN & ENTITAS PENGGUNA
        </th>
    </tr>
    <tr style="background-color: #4C1D95; color: #FFFFFF; font-weight: bold; text-align: center;">
        <th>No</th>
        <th>Layanan</th>
        <th>Mahasiswa</th>
        <th>Dosen</th>
        <th>Tendik</th>
        <th>Karyawan</th>
        <th>Superuser</th>
        <th>Tamu</th>
        <th>Lainnya</th>
        <th>Total</th>
        <th>% dari Total</th>
    </tr>
    @php $no = 1; @endphp
    @foreach ($reportData as $row)
        <tr style="{{ $no % 2 === 0 ? 'background-color: #F5F3FF;' : '' }}">
            <td style="text-align: center;">{{ $no++ }}</td>
            <td>{{ $row['name'] }}</td>
            <td style="text-align: center;">{{ $row['entities']['mahasiswa'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['dosen'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['tendik'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['karyawan'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['superuser'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['tamu'] ?? 0 }}</td>
            <td style="text-align: center;">{{ $row['entities']['lainnya'] ?? 0 }}</td>
            <td style="text-align: center; font-weight: bold; color: #7C3AED;">{{ $row['total'] }}</td>
            <td style="text-align: center;">{{ $grandTotal > 0 ? round(($row['total'] / $grandTotal) * 100, 2) : 0 }}%
            </td>
        </tr>
    @endforeach
    <tr></tr>

    <tr>
        <th colspan="12"
            style="background-color: #065F46; color: #FFFFFF; font-weight: bold; font-size: 11px; padding: 6px;">
            DETAIL SELURUH TIKET
        </th>
    </tr>
    <tr style="background-color: #064E3B; color: #FFFFFF; font-weight: bold; text-align: center; font-size: 9px;">
        <th>No</th>
        <th>Kode Tiket</th>
        <th>Tanggal Masuk</th>
        <th>Nama Pemohon</th>
        <th>Entitas</th>
        <th>Layanan</th>
        <th>Petugas</th>
        <th>Prioritas</th>
        <th>Status</th>
        <th>Tgl Ditugaskan</th>
        <th>Tgl Selesai</th>
        <th>Durasi (Jam)</th>
    </tr>
    @foreach ($tickets as $index => $t)
        @php
            $duration = 0;
            if ($t->assigned_at && $t->closed_at) {
                $duration = $t->assigned_at->diffInHours($t->closed_at);
            }
            $name = $t->user ? $t->user->name : ($t->guestDetail ? $t->guestDetail->full_name : 'Tamu');

            $entity = 'Lainnya';
            if ($t->user) {
                $entity = match ($t->user->entity?->value ?? '') {
                    'mahasiswa' => 'Mahasiswa',
                    'dosen' => 'Dosen',
                    'tendik' => 'Tendik',
                    'karyawan' => 'Karyawan',
                    'superuser' => 'Superuser',
                    'tamu' => 'Tamu',
                    default => 'Lainnya',
                };
            } elseif ($t->guestDetail) {
                $entity = match ($t->guestDetail->entity_type?->value ?? '') {
                    'mahasiswa' => 'Mhs (Tamu)',
                    'dosen' => 'Dosen (Tamu)',
                    'tendik' => 'Tendik (Tamu)',
                    default => 'Tamu',
                };
            }
        @endphp
        <tr style="{{ ($index + 1) % 2 === 0 ? 'background-color: #ECFDF5;' : '' }}">
            <td style="text-align: center;">{{ $index + 1 }}</td>
            <td style="text-align: center; font-weight: bold;">#{{ $t->ticket_code }}</td>
            <td style="text-align: center;">{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $name }}</td>
            <td style="text-align: center;">{{ $entity }}</td>
            <td>{{ $t->service->name ?? '-' }}</td>
            <td>{{ $t->assignee->name ?? 'Belum Ditugaskan' }}</td>
            <td style="text-align: center;">{{ $t->priority->name ?? '-' }}</td>
            <td style="text-align: center;">{{ $t->status->name ?? '-' }}</td>
            <td style="text-align: center;">{{ $t->assigned_at ? $t->assigned_at->format('d/m/Y H:i') : '-' }}</td>
            <td style="text-align: center;">{{ $t->closed_at ? $t->closed_at->format('d/m/Y H:i') : '-' }}</td>
            <td style="text-align: center;">{{ $duration }}</td>
        </tr>
    @endforeach
</table>
