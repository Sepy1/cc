<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_no',
        'reporter_name',
        'phone',
        'email',
        'category',
        'title',
        'detail',
        'status',
        'assigned_to',
        'assigned_at',
        'tindak_lanjut',
        'reporter_type',
        'is_nasabah',
        'id_ktp',
        'nomor_rekening',
        'nama_ibu',
        'alamat',
        'kode_kantor',
        'attachment_ktp',
        'attachment_bukti',
        'media_closing',
        'closing_at',
        // menerima input API
        'tempat_lahir',
        'tgl_lahir',
    ];

    // jika ingin casting di masa depan
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'assigned_to' => 'integer',
        'assigned_at' => 'datetime',
        'is_nasabah' => 'boolean',
        'closing_at' => 'datetime',
        // cast ke date agar otomatis Carbon
        'tgl_lahir' => 'date',
    ];

    public function replies(): HasMany
{
    return $this->hasMany(TicketReply::class);
}

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status ?? 'Unknown');
    }

    public function events(): HasMany
{
    return $this->hasMany(\App\Models\TicketEvent::class, 'ticket_id')->latest();
}

// optional convenience helper
public function recordEvent(string $type, ?int $userId = null, array $meta = [])
{
    return $this->events()->create([
        'type' => $type,
        'user_id' => $userId,
        'meta' => $meta ?: null,
    ]);}

    /**
     * Helper untuk mengubah status dengan aman dan merekam event.
     */
    public function setStatus(string $status, ?int $userId = null, ?string $tindakLanjut = null): bool
    {
        $old = $this->status;
        $this->status = $status;

        // simpan tindak_lanjut ke kolom ticket jika diberikan
        if (!is_null($tindakLanjut)) {
            $this->tindak_lanjut = $tindakLanjut;
        }

        $saved = $this->save();

        if ($saved && method_exists($this, 'recordEvent')) {
            $meta = [
                'changes' => [
                    'status' => [
                        'from' => $old,
                        'to'   => $status,
                    ],
                ],
            ];

            // sertakan tindak_lanjut di meta jika ada
            if (!is_null($tindakLanjut) && $tindakLanjut !== '') {
                $meta['tindak_lanjut'] = $tindakLanjut;
            }

            $this->recordEvent('status_changed', $userId, $meta);
        }

        return $saved;
    }
}
