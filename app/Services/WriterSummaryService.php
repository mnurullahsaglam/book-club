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
        $this->setGuestStats();

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
                    } else {
                        dump('Unexpected guest format: ', $guest);
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

        return $summaryText;
    }
}
