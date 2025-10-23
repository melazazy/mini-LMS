<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $recordTitleAttribute = 'title';
    
    public function isTableReorderable(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) => 
                        $context === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null
                    ),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('video_url')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('hls_manifest_url')
                    ->url()
                    ->maxLength(255),
                Forms\Components\TextInput::make('duration_seconds')
                    ->numeric()
                    ->default(0)
                    ->suffix('seconds'),
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_published')
                    ->default(false),
                Forms\Components\Toggle::make('is_free_preview')
                    ->default(false),
                Forms\Components\KeyValue::make('resources')
                    ->keyLabel('Resource Name')
                    ->valueLabel('Resource URL'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Lesson Title')
                    ->limit(50),
                Tables\Columns\TextColumn::make('duration_seconds')
                    ->formatStateUsing(fn ($state) => gmdate('H:i:s', $state))
                    ->label('Duration')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published')
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_free_preview')
                    ->boolean()
                    ->label('Free Preview')
                    ->alignCenter(),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('moveUp')
                    ->icon('heroicon-o-arrow-up')
                    ->color('gray')
                    ->action(function ($record) {
                        $previous = \App\Models\Lesson::where('course_id', $record->course_id)
                            ->where('order', '<', $record->order)
                            ->orderBy('order', 'desc')
                            ->first();
                        
                        if ($previous) {
                            $temp = $record->order;
                            $record->order = $previous->order;
                            $previous->order = $temp;
                            $record->save();
                            $previous->save();
                        }
                    })
                    ->visible(fn ($record) => \App\Models\Lesson::where('course_id', $record->course_id)
                        ->where('order', '<', $record->order)->exists()),
                Tables\Actions\Action::make('moveDown')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->action(function ($record) {
                        $next = \App\Models\Lesson::where('course_id', $record->course_id)
                            ->where('order', '>', $record->order)
                            ->orderBy('order', 'asc')
                            ->first();
                        
                        if ($next) {
                            $temp = $record->order;
                            $record->order = $next->order;
                            $next->order = $temp;
                            $record->save();
                            $next->save();
                        }
                    })
                    ->visible(fn ($record) => \App\Models\Lesson::where('course_id', $record->course_id)
                        ->where('order', '>', $record->order)->exists()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
