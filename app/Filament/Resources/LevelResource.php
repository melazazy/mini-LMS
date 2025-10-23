<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Levels';

    protected static ?string $pluralModelLabel = 'Levels';

    public static function table(Table $table): Table
    {
        return $table
            ->query(Course::query()->selectRaw('level as id, level, COUNT(*) as courses_count')->groupBy('level'))
            ->columns([
                Tables\Columns\TextColumn::make('level')
                    ->label('Level')
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'beginner' => 'success',
                        'intermediate' => 'warning',
                        'advanced' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('courses_count')
                    ->label('Total Courses')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view_courses')
                    ->label('View Courses')
                    ->icon('heroicon-o-academic-cap')
                    ->url(fn ($record): string => route('filament.admin.resources.courses.index', ['tableFilters' => ['level' => ['value' => $record->level]]]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('level')
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLevels::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canView($record): bool
    {
        return false;
    }
}
