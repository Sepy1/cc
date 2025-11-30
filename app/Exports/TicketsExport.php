<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TicketsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tickets;

    /**
     * @param \Illuminate\Support\Collection $tickets
     */
    public function __construct(Collection $tickets)
    {
        $this->tickets = $tickets;
    }

    /**
     * Return collection to be exported
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->tickets;
    }

    /**
     * Map each row
     */
    public function map($ticket): array
    {
        // Use the exact fields you provided. Provide fallbacks where appropriate.
        return [
            $ticket->ticket_no,
            $ticket->reporter_name ?? '',
            $ticket->phone ?? '',
            $ticket->email ?? '',
            $ticket->category ?? '',
            $ticket->title ?? '',
            $ticket->detail ?? '',
            ucfirst($ticket->status ?? ''),
            $ticket->assigned_to ?? '',
            $ticket->tindak_lanjut ?? '',
            $ticket->created_at ? $ticket->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * Headings for Excel
     */
    public function headings(): array
    {
        return [
            'No Tiket',
            'Pelapor',
            'Phone',
            'Email',
            'Kategori',
            'Judul',
            'Detail',
            'Status',
            'Assigned To',
            'Tindak Lanjut',
            'Tanggal Dibuat',
        ];
    }
}
