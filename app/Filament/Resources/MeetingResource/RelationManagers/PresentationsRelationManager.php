<?php

namespace App\Filament\Resources\MeetingResource\RelationManagers;

use App\Models\Meeting;
use App\Models\Presentation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
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
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('assign_to_another_user')
                        ->label('Başkasına Ata')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('success')
                        ->form([
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
                        ->form([
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
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
