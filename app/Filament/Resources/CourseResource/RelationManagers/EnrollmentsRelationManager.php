<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('course_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user.name')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'refunded',
                        'danger' => 'canceled',
                    ]),
                Tables\Columns\TextColumn::make('completion')
                    ->label('Progress')
                    ->getStateUsing(fn ($record) => $record->getCompletionPercentage())
                    ->formatStateUsing(fn ($state) => number_format($state, 1) . '%')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 50 => 'warning',
                        $state > 0 => 'info',
                        default => 'gray',
                    })
                    ->sortable(query: function ($query, string $direction) {
                        // This is a computed column, so we can't sort directly
                        // For now, we'll skip sorting or implement a custom solution
                        return $query;
                    }),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('Free'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'refunded' => 'Refunded',
                        'canceled' => 'Canceled',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('created_at', 'desc');
    }
}
