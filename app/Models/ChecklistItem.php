<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Per-project checklist state (guides/checklists.md). The item definitions —
 * groups, labels, probes — live in config/checklists.php; rows exist only for
 * items that have state (checked or probed).
 *
 * @property int $id
 * @property string $key
 * @property Carbon|null $checked_at
 * @property int|null $checked_by
 * @property bool|null $last_result
 * @property Carbon|null $last_run_at
 * @property string|null $detail
 */
#[Fillable(['key'])]
class ChecklistItem extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'last_result' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function isChecked(): bool
    {
        return $this->checked_at !== null;
    }
}
