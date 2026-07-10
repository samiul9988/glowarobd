<?php

namespace Modules\Waitlist\Services;

use Modules\Waitlist\Jobs\NotifyJob;
use Modules\Waitlist\Entities\Waitlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WaitlistService
{
    protected $model;
    public function __construct(Waitlist $model)
    {
        $this->model = $model;
    }

    public function getAll(array $filters, string $order = 'latest'): Collection
    {
        if (!in_array($order, ['latest', 'oldest'])) {
            $order = 'latest';
        }
        return $this->model->filtered($filters)->$order()->get();
    }

    public function paginated(array $filters, string $order = 'latest'): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 15;
        if (!in_array($order, ['latest', 'oldest'])) {
            $order = 'latest';
        }
        return $this->model->filtered($filters)->$order()->paginate($perPage);
    }

    /**
     * @param integer $productId
     * @param string $order in ['latest', 'oldest']
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getNotifiables(int $productId, string $order = 'latest'): Collection
    {
        if (!in_array($order, ['latest', 'oldest'])) {
            $order = 'latest';
        }

        return $this->model->where('product_id', $productId)
                        ->where('notified', false)
                        ->$order()
                        ->get();
    }

    public function create(array $data): Waitlist
    {
        $checkFor = [
            'product_id' => $data['product_id'],
            'contact' => $data['contact_type'] === 'phone' ? str_replace(['-', '+88'], '', $data['contact']) : $data['contact'],
            'contact_type' => $data['contact_type'],
        ];
        $updateAble = ['notified' => false, 'notified_at' => null, 'created_at' => now(), 'updated_at' => now()];
        return $this->model->updateOrCreate($checkFor, $updateAble);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete() > 0;
    }

    public function deleteAll(array $ids): bool
    {
        return $this->model->whereIn('id', $ids)->delete() > 0;
    }

    public function notify(int $id, bool $silent = false): bool
    {
        $waitlistEntry = $this->model->find($id);
        if (!$waitlistEntry) {
            return false;
        }
        $waitlistEntry->notified = true;
        $waitlistEntry->notified_at = now();
        $waitlistEntry->save();
        if (!$silent) {
            NotifyJob::dispatch($waitlistEntry->id);
        }
        return true;
    }

    public function notifyAll(array $ids, bool $silent = false): int
    {
        if (empty($ids)) { return 0; }

        $affected = $this->model->whereIn('id', $ids)->update([
            'notified' => true,
            'notified_at' => now(),
        ]);

        if (!$silent) {
            foreach ($ids as $id) {
                NotifyJob::dispatch($id);
            }
        }

        return $affected;
    }

    public function notifyNotifiables(int $productId): int
    {
        // Only fetch IDs (fast!)
        $ids = $this->model->where('product_id', $productId)
            ->where('notified', false)
            ->oldest()
            ->pluck('id');

        $affected = $this->model->where('product_id', $productId)
            ->where('notified', false)
            ->update([
                'notified' => true,
                'notified_at' => now(),
            ]);

        foreach ($ids as $id) {
            NotifyJob::dispatch($id);
        }

        return $affected; // number of entries notified
    }

}
