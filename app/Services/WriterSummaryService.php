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
        $this->summary['first_meeting'] = $this->writer->meetings()
            ->orderBy('date')
            ->first()
            ->date
            ->format('d/m/Y');
    }

    public function setLastMeetingDate(): void
    {
        $this->summary['last_meeting'] = $this->writer->meetings()
            ->orderByDesc('date')
            ->first()
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
        $this->summary['meetings'] = $this->writer->meetings()->get();
        $this->summary['meetings_count'] = $this->writer->meetings()->count();
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

        foreach ($this->writer->meetings as $meeting) {
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
        // Initialize an array to store user participation data
        $participationData = [];

        // Loop through each meeting associated with the writer
        foreach ($this->writer->meetings as $meeting) {
            // Loop through each user who abstained from this meeting
            foreach ($meeting->abstainedUsers as $user) {
                // Initialize user data if not set
                if (! isset($participationData[$user->id])) {
                    $participationData[$user->id] = [
                        'name' => $user->name,
                        'absence_count' => 0,
                        'reasons' => collect(),
                    ];
                }

                // Increment the absence count
                $participationData[$user->id]['absence_count'] += 1;

                // Add the reason for not participating
                $reasons = $user->pivot->reason_for_not_participating
                    ? collect(explode(',', $user->pivot->reason_for_not_participating))->unique()
                    : collect();
                $participationData[$user->id]['reasons'] = $participationData[$user->id]['reasons']->merge($reasons)->unique();
            }
        }

        // Collect all users who were part of any meeting
        $allUsers = $this->writer->meetings->flatMap(function ($meeting) {
            return $meeting->users;
        })->unique('id'); // Avoid duplicate users

        // Determine which users participated in all meetings
        $abstainedText = '';

        foreach ($allUsers as $user) {
            if (isset($participationData[$user->id])) {
                // This user missed some meetings
                $absenceCount = $participationData[$user->id]['absence_count'];
                $reasons = $participationData[$user->id]['reasons']->implode(', ');
                $abstainedText .= "{$user->name}; {$reasons} dolayısıyla toplam {$absenceCount} defa katılım gösteremedi. ";
            } else {
                // This user participated in all meetings
                $abstainedText .= "{$user->name} tüm toplantılara katılım sağladı. ";
            }
        }

        $this->summary['abstained_users'] = $abstainedText;
    }

    private function setPresentationList(): void
    {
        $presentationList = $this->writer->meetings->flatMap(function ($meeting) {
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
