<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Admins can do everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, WorkOrder $workOrder): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WorkOrder $workOrder): bool
    {
        // Cannot update completed/invoiced work orders
        $lockedStatuses = ['completed', 'invoiced', 'cancelled'];

        return ! in_array($workOrder->workOrderStatus?->code, $lockedStatuses, true);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WorkOrder $workOrder): bool
    {
        // Cannot delete invoiced work orders
        if ($workOrder->workOrderStatus?->code === 'invoiced') {
            return false;
        }

        // Cannot delete if has invoice
        if ($workOrder->invoice_id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can complete the work order.
     */
    public function complete(User $user, WorkOrder $workOrder): bool
    {
        $lockedStatuses = ['completed', 'invoiced', 'cancelled'];

        return ! in_array($workOrder->workOrderStatus?->code, $lockedStatuses, true);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WorkOrder $workOrder): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WorkOrder $workOrder): bool
    {
        return false;
    }
}
