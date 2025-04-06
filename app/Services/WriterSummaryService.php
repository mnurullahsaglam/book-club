<?php

namespace App\Services;

use App\Models\Writer;

class WriterSummaryService
{
    private array $summary;

    public function __construct(protected Writer $writer) {}

    public function handle(): string
    {
        $this->setFirstMeetingDate();
        $this->setLastMeetingDate();

        $this->setBookStats();
        $this->setMeetingStats();
        $this->setLocationStats();
        $this->setAbstainedUserStats();
        $this->setGuestStats();
        $this->setPresentationList();

        return $this->generateSummaryText();
    }

    public function setFirstMeetingDate(): void
    {
        $this->summary['first_meeting'] = $this->writer->allRelatedMeetings()
            ->first()
            ->date
            ->format('d/m/Y');
    }

    public function setLastMeetingDate(): void
    {
        $this->summary['last_meeting'] = $this->writer->allRelatedMeetings()
            ->last()
            ->date
            ->format('d/m/Y');
    }

    private function setBookStats(): void
    {
        $this->summary['books'] = $this->writer->books;
        $this->summary['books_count'] = $this->writer->books->count();
    }

    private function setMeetingStats(): void
    {
        $this->summary['meetings'] = $this->writer->allRelatedMeetings();
        $this->summary['meetings_count'] = $this->writer->allRelatedMeetings()->count();
    }

    private function setLocationStats(): void
    {
        $this->summary['locations_text'] = $this->summary['meetings']->groupBy('location')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->map(function ($count, $location) {
                return "{$location} ({$count})";
            })
            ->join(', ');

        $this->summary['locations_count'] = $this->summary['meetings']->groupBy('location')->count();
    }

    private function setGuestStats(): void
    {
        $guestCounts = collect();
        $totalGuests = 0;

        foreach ($this->writer->allRelatedMeetings() as $meeting) {
            $guests = collect($meeting->guests ?? []);
            if ($guests->isNotEmpty()) {
                foreach ($guests as $guest) {
                    if (is_array($guest) && isset($guest['name'])) {
                        $name = $guest['name'];
                        $guestCounts->put($name, $guestCounts->get($name, 0) + 1);
                        $totalGuests++;
                    }
                }
            }
        }

        $sortedGuests = $guestCounts->sortDesc()->map(function ($count, $guest) {
            return "$guest ($count)";
        })
            ->implode(', ');

        $this->summary['guests_text'] = $sortedGuests;
        $this->summary['guests_count'] = $totalGuests;
    }

    private function generateSummaryText(): string
    {
        $summaryText = $this->writer->personal_information_text;

        $summaryText .= 'İlk toplantı tarihi: '.$this->summary['first_meeting'].'<br>';
        $summaryText .= 'Son toplantı tarihi: '.$this->summary['last_meeting'].'<br>';

        $summaryText .= 'Toplam toplantı sayısı: '.$this->summary['meetings_count'].'<br>';
        $summaryText .= 'Toplam kitap sayısı: '.$this->summary['books_count'].'<br>';

        $summaryText .= 'Mekanlar ('.$this->summary['locations_count'].'): '.$this->summary['locations_text'].'<br>';
        $summaryText .= 'Misafirler ('.$this->summary['guests_count'].'): '.$this->summary['guests_text'].'<br>';
        $summaryText .= 'Katılım Durumu: '.$this->summary['abstained_users'].'<br>';

        $summaryText .= 'Sunumlar: <br>'.$this->summary['presentation_list']->implode('<br>');

        return $summaryText;
    }

    private function setAbstainedUserStats(): void
    {
        $participationData = [];

        foreach ($this->writer->allRelatedMeetings() as $meeting) {
            foreach ($meeting->abstainedUsers as $user) {
                if (! isset($participationData[$user->id])) {
                    $participationData[$user->id] = [
                        'name' => $user->name,
                        'absence_count' => 0,
                        'reasons' => collect(),
                    ];
                }

                $participationData[$user->id]['absence_count'] += 1;

                $reasons = $user->pivot->reason_for_not_participating
                    ? collect(explode(',', $user->pivot->reason_for_not_participating))->map(fn ($r) => trim($r))->unique()
                    : collect();

                $participationData[$user->id]['reasons'] = $participationData[$user->id]['reasons']->merge($reasons)->unique();
            }
        }

        $allUsers = $this->writer->allRelatedMeetings()->flatMap(function ($meeting) {
            return $meeting->users;
        })->unique('id');

        $fullParticipants = collect();
        $abstainers = collect();

        foreach ($allUsers as $user) {
            if (isset($participationData[$user->id])) {
                $absenceCount = $participationData[$user->id]['absence_count'];
                $reasons = $participationData[$user->id]['reasons']->implode(', ');
                $abstainers->push([
                    'name' => $user->name,
                    'text' => "{$user->name}; {$reasons} dolayısıyla toplam {$absenceCount} defa",
                ]);
            } else {
                $fullParticipants->push($user->name);
            }
        }

        $fullParticipants = $fullParticipants->sort()->values();
        $abstainers = $abstainers->sortBy('name')->pluck('text');

        $abstainedText = '';

        if ($fullParticipants->isNotEmpty()) {
            $abstainedText .= $fullParticipants->implode(', ') . " tüm toplantılara katılım sağladı. ";
        }

        if ($abstainers->isNotEmpty()) {
            $abstainedText .= $abstainers->implode(', ') . " katılım gösteremedi.";
        }

        $this->summary['abstained_users'] = $abstainedText;
    }

    private function setPresentationList(): void
    {
        $presentationList = $this->writer->allRelatedMeetings()->flatMap(function ($meeting) {
            return collect($meeting->presentations ?? [])
                ->filter()
                ->map(function ($presentation) {
                    $item = '- '.$presentation->title;

                    if ($presentation->author) {
                        $item .= ', <i>'.$presentation->author.'</i>';
                    }

                    if ($presentation->publication_year) {
                        $item .= ', '.$presentation->publication_year;
                    }

                    return $item;
                });
        });

        $this->summary['presentation_list'] = $presentationList;
    }
}
