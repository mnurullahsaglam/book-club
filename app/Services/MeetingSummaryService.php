<?php

namespace App\Services;

use App\Models\Meeting;

class MeetingSummaryService
{
    public function __construct(protected Meeting $meeting)
    {
        $this->meeting->loadMissing('users', 'abstainedUsers', 'presentations', 'meetable');
    }

    public function handle(): string
    {
        $summaryText = $this->meeting->ordered_title;

        $summaryText .= '<br>';

        $summaryText .= '<b>Tarih:</b> ' . $this->meeting->date->format('d/m/Y') . '<br>';
        $summaryText .= '<b>Yer:</b> ' . $this->meeting->location . '<br>';
        $summaryText .= '<b>Katılımcılar:</b> ' . implode(', ', $this->meeting->users->pluck('name')->toArray()) . '<br>';
        $summaryText .= '<b>Toplantıya katılmayanlar:</b> ' . implode(', ', $this->meeting->abstainedUsers->pluck('name')->toArray()) . '<br>';
        $summaryText .= '<b>Sunumlar:</b> ' . implode('<br>', $this->meeting->presentations->pluck('title')->toArray()) . '<br>';
        $summaryText .= '<b>Toplantı konuları:</b> ' . $this->meeting->topics . '<br>';
        $summaryText .= '<b>Kararlar:</b> ' . $this->meeting->decisions . '<br>';

        return $summaryText;
    }
}