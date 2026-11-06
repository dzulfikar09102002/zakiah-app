<?php

namespace App\Services;

use App\Enums\OpnameSessionStatus;
use App\Enums\OpnameSessionType;
use App\Models\Employee;
use App\Models\ProductOpnameService as ProductOpnameDocument;
use App\Models\ProductOpnameSession;
use App\Services\Concerns\HandlesStockDocuments;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductOpnameSessionService
{
    use HandlesStockDocuments;

    /** Nama role (huruf kecil) yang boleh menjadi PJ atau ikut SO. */
    private const OPNAME_ROLE_NAMES = ['office', 'ceo', 'entity owner'];

    /** @var array<int, array<int, int>> */
    private array $currentEmployeeIds = [];

    public function __construct(
        protected ProductOpnameReviewService $reviews,
    ) {}

    public function getSessions(): LengthAwarePaginator
    {
        $search = request('search', '');
        $status = request('status');
        $startAt = request('start_at') ? Carbon::parse(request('start_at'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $endAt = request('end_at') ? Carbon::parse(request('end_at'))->endOfDay() : null;

        $selectAll = request('select_all_location', '1') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        return ProductOpnameSession::query()
            ->where('entity_id', $this->entityId())
            ->with(['location:id,name', 'pic:id,first_name,last_name', 'employees:id'])
            ->withCount('employees')
            ->when($search, fn (Builder $query) => $query->whereLike('code', "%{$search}%"))
            ->when(is_string($status), fn (Builder $query) => $query->withStatus($status))
            ->where('opname_date', '>=', $startAt->toDateString())
            ->when($endAt, fn (Builder $query) => $query->where('opname_date', '<=', $endAt->toDateString()))
            ->when($selectAll && count($excludeLocs), fn (Builder $query) => $query->whereNotIn('location_id', $excludeLocs))
            ->when(! $selectAll && count($locs), fn (Builder $query) => $query->whereIn('location_id', $locs))
            ->latest('id')
            ->paginate(request('per_page', 10))
            ->withQueryString()
            ->through(function (ProductOpnameSession $session) {
                $session->setAttribute('can_enter', $this->isMember($session));
                $session->setRelation('employees', collect());

                return $session;
            });
    }

    public function find(int $id): ProductOpnameSession
    {
        // Sesi milik usaha lain (mis. QR dibuka akun lain): ditolak dengan jelas, bukan "tidak ditemukan".
        abort_if(
            ProductOpnameSession::query()->whereKey($id)->where('entity_id', '<>', $this->entityId())->exists(),
            403,
            'Anda tidak terdaftar pada sesi SO ini.',
        );

        return ProductOpnameSession::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'location:id,name',
                'pic:id,first_name,last_name',
                'employees:id,first_name,last_name',
                'parent:id,code,entity_id,location_id',
                'children:id,parent_session_id,code,type,opname_date,start_at,review_at,end_at',
            ])
            ->findOrFail($id);
    }

    public function store(array $data): ProductOpnameSession
    {
        $employeeId = $this->employeeId();

        return DB::transaction(function () use ($data, $employeeId) {
            $startNow = (bool) ($data['start_now'] ?? false);

            if ($startNow) {
                $this->ensureLocationFree($data['location_id']);
            }

            $session = ProductOpnameSession::create([
                'entity_id' => $this->entityId(),
                'location_id' => $data['location_id'],
                'type' => OpnameSessionType::Opname->value,
                'code' => $this->generateCode('SO', $data['opname_date']),
                'opname_date' => $data['opname_date'],
                'pic_employee_id' => $data['pic_employee_id'],
                'start_at' => $startNow ? now() : null,
                'note' => $data['note'] ?? null,
                'ins_date' => now(),
                'ins_by' => $employeeId,
            ]);

            $this->syncEmployees($session, $data['employee_ids'], $employeeId);

            return $session;
        });
    }

    public function update(ProductOpnameSession $session, array $data): ProductOpnameSession
    {
        $this->ensureSessionStatus($session, OpnameSessionStatus::Scheduled);
        $employeeId = $this->employeeId();

        return DB::transaction(function () use ($session, $data, $employeeId) {
            $startNow = (bool) ($data['start_now'] ?? false);

            if ($startNow) {
                $this->ensureLocationFree($data['location_id']);
            }

            $session->update([
                'location_id' => $data['location_id'],
                'opname_date' => $data['opname_date'],
                'pic_employee_id' => $data['pic_employee_id'],
                'start_at' => $startNow ? now() : null,
                'note' => $data['note'] ?? null,
                'upd_date' => now(),
                'upd_by' => $employeeId,
            ]);

            $this->syncEmployees($session, $data['employee_ids'], $employeeId);

            return $session;
        });
    }

    public function destroy(ProductOpnameSession $session): void
    {
        $this->ensureSessionStatus($session, OpnameSessionStatus::Scheduled);

        // Baris karyawan ikut terhapus lewat ON DELETE CASCADE.
        DB::transaction(function () use ($session) {
            DB::table('product_opname_session_employees')->where('session_id', $session->id)->delete();
            $session->delete();
        });
    }

    public function start(ProductOpnameSession $session): void
    {
        $this->ensureSessionStatus($session, OpnameSessionStatus::Scheduled);

        DB::transaction(function () use ($session) {
            $this->ensureLocationFree($session->location_id, $session->id);
            $this->touchSession($session, ['start_at' => now()]);
        });
    }

    public function closeScan(ProductOpnameSession $session): void
    {
        $this->ensureSessionStatus($session, OpnameSessionStatus::Started);
        $this->ensureOpnameType($session);

        $this->touchSession($session, ['review_at' => now()]);
    }

    /**
     * Buka scan lagi untuk hitung ulang. Produk yang sudah diputuskan tetap final.
     */
    public function reopenScan(ProductOpnameSession $session): void
    {
        $this->ensureSessionStatus($session, OpnameSessionStatus::Review);

        $this->touchSession($session, ['review_at' => null]);
    }

    public function finish(ProductOpnameSession $session): void
    {
        DB::transaction(function () use ($session) {
            $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);

            if ($locked->sessionType() === OpnameSessionType::Revision) {
                $this->ensureSessionStatus($locked, OpnameSessionStatus::Started);

                if (! DB::table('product_opname_session_revisions')->where('session_id', $locked->id)->exists()) {
                    throw ValidationException::withMessages([
                        'session' => 'Belum ada hasil revisi yang disimpan.',
                    ]);
                }
            } else {
                $this->ensureSessionStatus($locked, OpnameSessionStatus::Review);

                $pending = $this->reviews->summary($locked)['pending'];

                if ($pending > 0) {
                    throw ValidationException::withMessages([
                        'session' => "Masih ada {$pending} produk selisih yang belum diputuskan.",
                    ]);
                }
            }

            $this->touchSession($locked, ['end_at' => now()]);
        });
    }

    /**
     * Sesi yang sudah berjalan tapi belum punya data boleh dibatalkan PJ, supaya sesi
     * salah buat tidak mengunci tokonya. Yang sudah ada datanya harus lewat review.
     */
    public function canCancel(ProductOpnameSession $session): bool
    {
        return $this->isPic($session)
            && in_array($session->currentStatus(), [OpnameSessionStatus::Started, OpnameSessionStatus::Review], true)
            && ! $this->hasRecordedData($session);
    }

    public function cancel(ProductOpnameSession $session): void
    {
        DB::transaction(function () use ($session) {
            $locked = ProductOpnameSession::query()->lockForUpdate()->findOrFail($session->id);

            if (! in_array($locked->currentStatus(), [OpnameSessionStatus::Started, OpnameSessionStatus::Review], true)) {
                throw ValidationException::withMessages([
                    'session' => 'Status sesi tidak valid untuk aksi ini. Muat ulang halaman.',
                ]);
            }

            if ($this->hasRecordedData($locked)) {
                throw ValidationException::withMessages([
                    'session' => 'Sesi sudah punya hasil scan atau keputusan, jadi tidak bisa dibatalkan.',
                ]);
            }

            DB::table('product_opname_session_employees')->where('session_id', $locked->id)->delete();
            $locked->delete();
        });
    }

    private function hasRecordedData(ProductOpnameSession $session): bool
    {
        return $session->product_opname_service_id !== null
            || $session->product_adjustment_stock_id !== null
            || DB::table('product_opname_session_scans')->where('session_id', $session->id)->exists()
            || DB::table('product_opname_session_reviews')->where('session_id', $session->id)->exists()
            || DB::table('product_opname_session_revisions')->where('session_id', $session->id)->exists();
    }

    /**
     * Buat sesi Revisi SO dari sesi SO yang sudah selesai. Revisi langsung berjalan
     * dan hanya berisi produk selisih yang diterima pada sesi asal.
     */
    public function createRevision(ProductOpnameSession $parent): ProductOpnameSession
    {
        $this->ensureSessionStatus($parent, OpnameSessionStatus::Finished);
        $this->ensureOpnameType($parent);
        $employeeId = $this->employeeId();

        return DB::transaction(function () use ($parent, $employeeId) {
            if (! $this->reviews->attachmentRows($parent)->count()) {
                throw ValidationException::withMessages([
                    'session' => 'Tidak ada produk selisih yang perlu direvisi.',
                ]);
            }

            $this->ensureLocationFree($parent->location_id);

            $session = ProductOpnameSession::create([
                'entity_id' => $parent->entity_id,
                'location_id' => $parent->location_id,
                'type' => OpnameSessionType::Revision->value,
                'parent_session_id' => $parent->id,
                'code' => $this->generateCode('RSO', now()->toDateString()),
                'opname_date' => now()->toDateString(),
                'pic_employee_id' => $parent->pic_employee_id,
                'start_at' => now(),
                'ins_date' => now(),
                'ins_by' => $employeeId,
            ]);

            $this->syncEmployees($session, [$parent->pic_employee_id], $employeeId);

            return $session;
        });
    }

    public function isMember(ProductOpnameSession $session): bool
    {
        $employeeIds = $this->currentEmployeeIds();

        return in_array((int) $session->pic_employee_id, $employeeIds, true)
            || $session->employees->pluck('id')->map(fn ($id) => (int) $id)->intersect($employeeIds)->isNotEmpty();
    }

    public function isPic(ProductOpnameSession $session): bool
    {
        return in_array((int) $session->pic_employee_id, $this->currentEmployeeIds(), true);
    }

    /**
     * Selama scan dibuka, semua anggota sesi boleh melihat rincian dan membatalkan scan.
     * Setelah scan ditutup, hanya PJ yang boleh.
     */
    public function canCorrect(ProductOpnameSession $session): bool
    {
        if ($session->sessionType() === OpnameSessionType::Revision) {
            return false;
        }

        return match ($session->currentStatus()) {
            OpnameSessionStatus::Started => $this->isMember($session),
            OpnameSessionStatus::Review => $this->isPic($session),
            default => false,
        };
    }

    public function canManageSchedule(ProductOpnameSession $session): bool
    {
        $employeeIds = $this->currentEmployeeIds();

        return in_array((int) $session->pic_employee_id, $employeeIds, true)
            || in_array((int) $session->ins_by, $employeeIds, true);
    }

    public function getLocationOptions(): Collection
    {
        return (new LocationService)->getLocationOptions();
    }

    /**
     * Karyawan yang bisa menjadi PJ atau ikut SO: hanya role Office dan CEO.
     * Role pemilik entity bernama "Entity Owner", jadi ikut dihitung sebagai CEO.
     *
     * Satu akun bisa punya beberapa baris karyawan, jadi ditampilkan satu baris per akun.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getEmployeeOptions(): Collection
    {
        return Employee::query()
            ->where('entity_id', $this->entityId())
            ->whereHas('role', function (Builder $query) {
                $query->whereIn(DB::raw('LOWER(name)'), self::OPNAME_ROLE_NAMES);
            })
            ->orderBy('id')
            ->get(['id', 'user_id', 'first_name', 'last_name'])
            ->unique('user_id')
            ->map(fn (Employee $employee) => [
                'value' => $employee->id,
                'label' => trim($employee->first_name.' '.$employee->last_name),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * Baris pilihan milik akun yang sedang login, dipakai sebagai PJ awal di form.
     */
    public function currentEmployeeOption(): ?int
    {
        return $this->getEmployeeOptions()
            ->pluck('value')
            ->intersect($this->currentEmployeeIds())
            ->first();
    }

    /**
     * Semua baris karyawan milik akun yang sedang login. Relasi user->employee hanya
     * mengembalikan salah satunya, padahal yang dipilih di sesi bisa baris yang lain.
     *
     * @return array<int, int>
     */
    private function currentEmployeeIds(): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return [];
        }

        return $this->currentEmployeeIds[$userId] ??= Employee::query()
            ->where('entity_id', $this->entityId())
            ->where('user_id', $userId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Dokumen opname lama yang masih menunggu persetujuan, supaya tetap bisa diselesaikan.
     *
     * @return Collection<int, ProductOpnameDocument>
     */
    public function getPendingLegacyDocuments(): Collection
    {
        return ProductOpnameDocument::query()
            ->where('entity_id', $this->entityId())
            ->where('status', 'requested')
            ->latest('id')
            ->get(['id', 'code', 'location_id', 'local_requested_at']);
    }

    private function syncEmployees(ProductOpnameSession $session, array $employeeIds, int $insertedBy): void
    {
        DB::table('product_opname_session_employees')->where('session_id', $session->id)->delete();

        $ids = collect($employeeIds)
            ->push($session->pic_employee_id)
            ->map(fn ($id) => (int) $id)
            ->unique();

        $allowed = $this->getEmployeeOptions()->pluck('value');

        if ($ids->diff($allowed)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'employee_ids' => 'PJ dan karyawan harus ber-role Office atau CEO.',
            ]);
        }

        DB::table('product_opname_session_employees')->insert(
            $ids->map(fn (int $id) => [
                'session_id' => $session->id,
                'employee_id' => $id,
                'ins_date' => now(),
                'ins_by' => $insertedBy,
            ])->values()->all(),
        );
    }

    private function touchSession(ProductOpnameSession $session, array $attributes): void
    {
        $session->update([
            ...$attributes,
            'upd_date' => now(),
            'upd_by' => $this->employeeId(),
        ]);
    }

    /**
     * Satu toko hanya boleh punya satu sesi berjalan, supaya hitungan tidak bercampur.
     */
    private function ensureLocationFree(int $locationId, ?int $exceptId = null): void
    {
        $exists = ProductOpnameSession::query()
            ->where('location_id', $locationId)
            ->whereNotNull('start_at')
            ->whereNull('end_at')
            ->when($exceptId, fn (Builder $query) => $query->where('id', '<>', $exceptId))
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'session' => 'Masih ada sesi SO yang berjalan di toko ini. Selesaikan dulu sesi tersebut.',
            ]);
        }
    }

    private function ensureSessionStatus(ProductOpnameSession $session, OpnameSessionStatus $status): void
    {
        if ($session->currentStatus() !== $status) {
            throw ValidationException::withMessages([
                'session' => 'Status sesi tidak valid untuk aksi ini. Muat ulang halaman.',
            ]);
        }
    }

    private function ensureOpnameType(ProductOpnameSession $session): void
    {
        if ($session->sessionType() !== OpnameSessionType::Opname) {
            throw ValidationException::withMessages([
                'session' => 'Aksi ini hanya untuk sesi Stok Opname.',
            ]);
        }
    }

    private function generateCode(string $prefix, string $date): string
    {
        return $prefix.'-'.Carbon::parse($date)->format('ymd').'-'.Str::upper(Str::random(5));
    }
}
