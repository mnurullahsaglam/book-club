<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\Meeting;
use App\Models\Writer;
use Illuminate\Contracts\Database\Query\Builder;

class MeetingObserver
{
    public function saved(Meeting $meeting): void
    {
        $writerId = $this->resolveWriterId($meeting->meetable_type, $meeting->meetable_id);
        $this->reorderWriterMeetings($writerId);

        if ($meeting->wasChanged(['meetable_type', 'meetable_id'])) {
            $oldMeetableType = $meeting->getOriginal('meetable_type');
            $oldMeetableId = $meeting->getOriginal('meetable_id');

            if ($oldMeetableType && $oldMeetableId) {
                $oldWriterId = $this->resolveWriterId($oldMeetableType, $oldMeetableId);

                if ($oldWriterId !== $writerId) {
                    $this->reorderWriterMeetings($oldWriterId);
                }
            }
        }
    }

    public function deleted(Meeting $meeting): void
    {
        $writerId = $this->resolveWriterId($meeting->meetable_type, $meeting->meetable_id);
        $this->reorderWriterMeetings($writerId);
    }

    private function resolveWriterId(string $meetableType, int|string $meetableId): int
    {
        if ($meetableType === Book::class) {
            return Book::find($meetableId)->writer_id;
        }

        return (int) $meetableId;
    }

    private function reorderWriterMeetings(int $writerId): void
    {
        $meetings = Meeting::whereHasMorph(
            'meetable',
            [Book::class, Writer::class],
            function (Builder $query, string $type) use ($writerId) {
                $column = $type === Book::class ? 'writer_id' : 'id';
                $query->where($column, $writerId);
            }
        )
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        foreach ($meetings as $index => $m) {
            if ($m->order !== $index + 1) {
                $m->updateQuietly(['order' => $index + 1]);
            }
        }
    }
}
