<?php

namespace App\Services;

use App\Models\Writer;

class WriterSummaryService
{
    private array $summary;

    public function __construct(protected Writer $writer)
    {
        $this->writer->loadMissing('books', 'readBooks', 'books.reviews', 'meetings', 'meetings.users', 'meetings.participatedUsers', 'meetings.abstainedUsers', 'meetings.presentations');
    }

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
        $this->setBookList();
        $this->setPageStats();
        $this->setOverallRating();

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

        $summaryText .= '<b>İlk toplantı tarihi:</b> '.$this->summary['first_meeting'].'<br>';
        $summaryText .= '<b>Son toplantı tarihi:</b> '.$this->summary['last_meeting'].'<br>';

        $summaryText .= '<b>Toplam toplantı sayısı:</b> '.$this->summary['meetings_count'].'<br>';
        $summaryText .= '<b>Toplam kitap sayısı:</b> '.$this->summary['books_count'].'<br>';

        if ($this->summary['total_pages'] > 0) {
            $summaryText .= '<b>Toplam Sayfa:</b> '.$this->summary['total_pages'];
            $summaryText .= ' (Ortalama: '.$this->summary['average_pages'].' sayfa/kitap)<br>';
        }

        if ($this->summary['overall_rating']) {
            $summaryText .= '<b>Genel Ortalama Puan:</b> '.$this->summary['overall_rating'];
            $summaryText .= ' ('.$this->summary['total_reviews_count'].' değerlendirme)<br>';
        }

        $summaryText .= '<b>Mekanlar ('.$this->summary['locations_count'].'</b>): '.$this->summary['locations_text'].'<br>';
        $summaryText .= '<b>Misafirler ('.$this->summary['guests_count'].'</b>): '.$this->summary['guests_text'].'<br>';
        $summaryText .= '<b>Katılım Durumu:</b> '.$this->summary['abstained_users'].'<br>';

        $summaryText .= '<b>Kitaplar:</b> <br>'.$this->summary['book_list']->implode('<br>');
        $summaryText .= '<br>';
        $summaryText .= '<b>Sunumlar:</b> <br>'.$this->summary['presentation_list']->implode('<br>');

        return $summaryText;
    }

    private function setAbstainedUserStats(): void
    {
        $totalMeetings = $this->writer->allRelatedMeetings()->count();
        $userParticipation = [];

        // Count participation and absences for each user
        foreach ($this->writer->allRelatedMeetings() as $meeting) {
            // Track all users involved in this meeting
            foreach ($meeting->users as $user) {
                if (! isset($userParticipation[$user->id])) {
                    $userParticipation[$user->id] = [
                        'name' => $user->name,
                        'total_meetings' => 0,
                        'participated' => 0,
                        'absent' => 0,
                        'absence_reasons' => collect(),
                    ];
                }
                $userParticipation[$user->id]['total_meetings'] += 1;
            }

            // Track who participated
            foreach ($meeting->participatedUsers as $user) {
                if (isset($userParticipation[$user->id])) {
                    $userParticipation[$user->id]['participated'] += 1;
                }
            }

            // Track absences with reasons
            foreach ($meeting->abstainedUsers as $user) {
                if (isset($userParticipation[$user->id])) {
                    $userParticipation[$user->id]['absent'] += 1;

                    $reasons = $user->pivot->reason_for_not_participating
                        ? collect(explode(',', $user->pivot->reason_for_not_participating))->map(fn ($r) => trim($r))
                        : collect();

                    $userParticipation[$user->id]['absence_reasons'] = $userParticipation[$user->id]['absence_reasons']->merge($reasons);
                }
            }
        }

        $participationTexts = collect();

        foreach ($userParticipation as $userData) {
            $name = $userData['name'];
            $userMeetings = $userData['total_meetings'];
            $participated = $userData['participated'];
            $absent = $userData['absent'];

            $text = "{$name} {$userMeetings} toplantının {$participated} tanesine katıldı.";

            if ($absent > 0) {
                // Group absence reasons by reason and count
                $reasonCounts = $userData['absence_reasons']
                    ->countBy()
                    ->sortDesc();

                if ($reasonCounts->isNotEmpty()) {
                    $reasonTexts = $reasonCounts->map(function ($count, $reason) {
                        return "{$count} defa {$reason} sebebiyle";
                    })->values();

                    $text .= ' '.$reasonTexts->implode(', ').' toplantılara katılamadı.';
                }
            }

            $participationTexts->push($text);
        }

        $this->summary['abstained_users'] = $participationTexts->sortBy(fn ($text) => $text)->implode(' ');
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

    private function setBookList(): void
    {
        $booksWithRatings = $this->writer->books->map(function ($book) {
            $reviewCount = $book->reviews->filter(fn ($r) => $r->rating !== null)->count();
            $averageRating = $reviewCount > 0 ? round($book->reviews->avg('rating'), 1) : null;

            return [
                'book' => $book,
                'rating' => $averageRating,
                'review_count' => $reviewCount,
            ];
        })->sortByDesc(function ($item) {
            // Sort by rating (nulls last), then by name
            return $item['rating'] ?? -1;
        });

        $bookList = $booksWithRatings->map(function ($item) {
            $book = $item['book'];
            $text = '- '.$book->name;

            if ($item['rating'] !== null) {
                $text .= " ({$item['rating']} puan/{$item['review_count']} değerlendirme)";
            } else {
                $text .= ' (Değerlendirilmemiş)';
            }

            return $text;
        });

        $this->summary['book_list'] = $bookList;
    }

    private function setPageStats(): void
    {
        $books = $this->writer->books->filter(fn ($book) => $book->page_count);

        $this->summary['total_pages'] = $books->sum('page_count');
        $this->summary['average_pages'] = $books->isNotEmpty()
            ? round($books->avg('page_count'))
            : 0;
    }

    private function setOverallRating(): void
    {
        $allReviews = $this->writer->books->flatMap(fn ($book) => $book->reviews);
        $reviewsWithRating = $allReviews->filter(fn ($review) => $review->rating !== null);

        if ($reviewsWithRating->isNotEmpty()) {
            $this->summary['overall_rating'] = round($reviewsWithRating->avg('rating'), 1);
            $this->summary['total_reviews_count'] = $reviewsWithRating->count();
        } else {
            $this->summary['overall_rating'] = null;
            $this->summary['total_reviews_count'] = 0;
        }
    }
}
