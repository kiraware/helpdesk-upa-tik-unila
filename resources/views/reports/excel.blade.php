<table>
    <tr>
        <th colspan="8" style="text-align: center; font-weight: bold; font-size: 14px;">
            REKAP LAPORAN TICKET HELPDESK TIK UNILA ({{ $startDate->format('d/m/Y') }} -
            {{ $endDate->format('d/m/Y') }})
        </th>
    </tr>
    <tr></tr>

    <tr style="background-color: #f3f4f6; font-weight: bold;">
        <th>No</th>
        <th>Layanan</th>
        <th>Total</th>
        <th>Done</th>
        <th>Reject</th>
    </tr>
    @php
        $no = 1;
        $grandTotal = 0;
        $grandDone = 0;
        $grandReject = 0;
    @endphp
    @foreach ($reportData as $row)
        @php
            $grandTotal += $row['total'];
            $grandDone += $row['done'];
            $grandReject += $row['reject'];
        @endphp
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['total'] }}</td>
            <td>{{ $row['done'] }}</td>
            <td>{{ $row['reject'] }}</td>
        </tr>
    @endforeach
    <tr style="font-weight: bold;">
        <td colspan="2">Total</td>
        <td>{{ $grandTotal }}</td>
        <td>{{ $grandDone }}</td>
        <td>{{ $grandReject }}</td>
    </tr>

    <tr></tr>
    <tr></tr>

    <tr style="background-color: #f3f4f6; font-weight: bold;">
        <th>No</th>
        <th>Layanan</th>
        <th>Tendik (T)</th>
        <th>Dosen (D)</th>
        <th>Mahasiswa (M)</th>
        <th>Lainnya</th>
        <th>Total</th>
        <th>% dari Total Keseluruhan</th>
    </tr>
    @php $no = 1; @endphp
    @foreach ($reportData as $row)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $row['name'] }}</td>
            <td>{{ $row['T'] }}</td>
            <td>{{ $row['D'] }}</td>
            <td>{{ $row['M'] }}</td>
            <td>{{ $row['L'] }}</td>
            <td>{{ $row['total'] }}</td>
            <td>{{ $grandTotal > 0 ? round(($row['total'] / $grandTotal) * 100, 2) : 0 }}%</td>
        </tr>
    @endforeach

    <tr></tr>
    <tr></tr>

    <tr style="background-color: #f3f4f6; font-weight: bold;">
        <th>No</th>
        <th>Ticket</th>
        <th>Date</th>
        <th>Name</th>
        <th>Service</th>
        <th>Assigned to</th>
        <th>Priority</th>
        <th>Ticket Duration (Jam)</th>
        <th>Status</th>
    </tr>
    @foreach ($tickets as $index => $t)
        @php
            $duration = 0;
            if ($t->assigned_at && $t->closed_at) {
                $duration = $t->assigned_at->diffInHours($t->closed_at);
            }
            $name = $t->user ? $t->user->name : ($t->guestDetail ? $t->guestDetail->full_name : 'Tamu');
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>#{{ $t->ticket_code }}</td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $name }}</td>
            <td>{{ $t->service->name ?? '-' }}</td>
            <td>{{ $t->assignee->name ?? '-' }}</td>
            <td>{{ $t->priority->name ?? '-' }}</td>
            <td>{{ $duration }} Jam</td>
            <td>{{ $t->status->name ?? '-' }}</td>
        </tr>
    @endforeach
</table>
