<?php

namespace App\Filament\Exports;

use App\Models\Book;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BookExporter extends Exporter
{
    protected static ?string $model = Book::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('writer.name')
                ->label('Yazar'),
            ExportColumn::make('publisher.name')
                ->label('Yayınevi'),
            ExportColumn::make('name')
                ->label('Kitap Adı'),
            ExportColumn::make('original_name')
                ->label('Orijinal Adı'),
            ExportColumn::make('page_count')
                ->label('Sayfa Sayısı'),
            ExportColumn::make('publication_date')
                ->label('Yayın Tarihi'),
            ExportColumn::make('publication_location')
                ->label('Yayın Yeri'),
            ExportColumn::make('reviews_avg_age')->avg('reviews', 'rating')
                ->label('Ortalama Değerlendirme Puanı'),
            ExportColumn::make('is_finished')
                ->label('Durum')
                ->formatStateUsing(fn (Book $record) => $record->is_finished ? 'Tamamlandı' : 'Tamamlanmadı'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Kitapların dışarı aktarılması tamamlandı. '.number_format($export->successful_rows).' kitap dışarı aktarıldı.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' kitap dışarı aktarılamadı.';
        }

        return $body;
    }
}
