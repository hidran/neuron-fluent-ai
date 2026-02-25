<?php

namespace App\Filament\Resources\ReadingPractices\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ReadingPracticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('readingCategory.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('language')
                    ->badge()
                    ->sortable(),
                TextColumn::make('generated_text')
                    ->label('Text')
                    ->limit(60)
                    ->wrap()
                    ->searchable()
                    ->tooltip(fn (?string $state): ?string => $state),
                TextColumn::make('recordings_count')
                    ->label('Recordings')
                    ->counts('recordings')
                    ->sortable(),
                ViewColumn::make('recordings_preview')
                    ->label('Saved Audios')
                    ->view('filament.resources.reading-practices.tables.columns.recordings-preview')
                    ->toggleable(),
                TextColumn::make('pronunciation_score')
                    ->label('Pronunciation')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('intonation_score')
                    ->label('Intonation')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('grammar_score')
                    ->label('Grammar')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('language')
                    ->options([
                        'en' => 'English',
                        'es' => 'Spanish',
                        'fr' => 'French',
                        'de' => 'German',
                        'it' => 'Italian',
                        'pt' => 'Portuguese',
                    ]),
                SelectFilter::make('reading_category_id')
                    ->relationship('readingCategory', 'name')
                    ->label('Category'),
            ])
            ->recordActions([
                Action::make('loadForRecording')
                    ->label('Load for Recording')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->url(fn (Model $record): string => route('filament.admin.pages.reading-practice', [
                        'session' => $record->getKey(),
                    ]))
                    ->visible(fn (Model $record): bool => (int) $record->getAttribute('user_id') === (int) Auth::id()),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
