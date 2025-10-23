<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LessonProgressResource\Pages;
use App\Models\LessonProgress;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonProgressResource extends Resource
{
    protected static ?string $model = LessonProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Lesson Progress';

    protected static ?string $pluralModelLabel = 'Lesson Progress';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('lesson_id')
                    ->relationship('lesson', 'title')
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('watched_percentage')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                Forms\Components\TextInput::make('last_position_seconds')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->suffix('seconds'),
                Forms\Components\DateTimePicker::make('last_watched_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lesson.course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Lesson')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('watched_percentage')
                    ->label('Progress')
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'warning',
                        $state > 0 => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Completed')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isCompleted())
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('watched_percentage', $direction);
                    }),
                Tables\Columns\TextColumn::make('last_position_seconds')
                    ->label('Last Position')
                    ->formatStateUsing(fn ($state) => gmdate('H:i:s', $state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_watched_at')
                    ->label('Last Watched')
                    ->dateTime()
                    ->sortable()
                    ->since(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Started At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Student')
                    ->relationship('user', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('lesson.course_id')
                    ->label('Course')
                    ->relationship('lesson.course', 'title')
                    ->searchable(),
                Tables\Filters\Filter::make('completed')
                    ->label('Completed Lessons')
                    ->query(fn (Builder $query): Builder => $query->where('watched_percentage', '>=', 90)),
                Tables\Filters\Filter::make('in_progress')
                    ->label('In Progress')
                    ->query(fn (Builder $query): Builder => $query->where('watched_percentage', '>', 0)->where('watched_percentage', '<', 90)),
                Tables\Filters\Filter::make('not_started')
                    ->label('Not Started')
                    ->query(fn (Builder $query): Builder => $query->where('watched_percentage', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('last_watched_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLessonProgress::route('/'),
            'create' => Pages\CreateLessonProgress::route('/create'),
            'view' => Pages\ViewLessonProgress::route('/{record}'),
            'edit' => Pages\EditLessonProgress::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('watched_percentage', '>=', 90)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
