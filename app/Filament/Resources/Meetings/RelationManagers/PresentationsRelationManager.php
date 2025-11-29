<?php

namespace App\Filament\Resources\Meetings\RelationManagers;

use App\Models\Meeting;
use App\Models\Presentation;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PresentationsRelationManager extends RelationManager
{
    protected static string $relationship = 'presentations';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $modelLabel = 'Sunum';

    protected static ?string $pluralLabel = 'Sunumlar';

    protected static ?string $title = 'Sunumlar';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->searchable(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Kişi')
                    ->searchable()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('assign_to_another_user')
                        ->label('Başkasına Ata')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('success')
                        ->schema([
                            Select::make('user_id')
                                ->relationship('user', 'name', fn (Builder $query) => $query->active())
                                ->label('Kişi')
                                ->required()
                                ->default(fn (Presentation $presentation) => $presentation->user_id)
                                ->columnSpanFull(),
                        ])
                        ->action(function (Presentation $presentation, array $data) {
                            $presentation->update([
                                'user_id' => $data['user_id'],
                            ]);

                            Notification::make()
                                ->title('Sunum Başka Bir Kişiye Atandı')
                                ->success()
                                ->send();
                        })
                        ->visible(auth()->user()->is_admin),
                    Action::make('assign_to_another_meeting')
                        ->label('Başka Toplantıya Ata')
                        ->icon('heroicon-o-adjustments-vertical')
                        ->color('gray')
                        ->schema([
                            Select::make('meeting_id')
                                ->relationship('meeting', 'title', fn (Builder $query) => $query->orderBy('order'))
                                ->label('Toplantı')
                                ->required()
                                ->default(fn (Presentation $presentation) => $presentation->meeting_id)
                                ->getOptionLabelFromRecordUsing(fn (Meeting $meeting) => "({$meeting->date->format('d/m/Y')}) {$meeting->ordered_title}")
                                ->columnSpanFull(),
                        ])
                        ->action(function (Presentation $presentation, array $data) {
                            $presentation->update([
                                'meeting_id' => $data['meeting_id'],
                            ]);

                            Notification::make()
                                ->title('Sunum Başka Bir Toplantıya Atandı')
                                ->success()
                                ->send();
                        })
                        ->visible(auth()->user()->is_admin),
                    Action::make('view')
                        ->label('Dosyayı görüntüle')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->url(fn ($record) => $record->file_url)
                        ->openUrlInNewTab(),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name', fn (Builder $query) => $query->active())
                    ->label('Kişi')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('citation')
                    ->label('Künye')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('author')
                    ->label('Müellif')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('publication_year')
                    ->label('Yayım Yılı')
                    ->numeric()
                    ->maxValue(date('Y'))
                    ->columnSpanFull(),

                FileUpload::make('file')
                    ->label('Dosya')
                    ->directory('presentations')
                    ->disk('public')
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Açıklama')
                    ->columnSpanFull(),
            ]);
    }
}
