<?php

namespace App\Filament\Resources;

use App\Actions\Certificate\GenerateCertificateAction;
use App\Events\CertificateIssued;
use App\Filament\Resources\CompletedCourseResource\Pages;
use App\Models\Certificate;
use App\Models\Enrollment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompletedCourseResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $modelLabel = 'Completed Course';

    protected static ?string $pluralModelLabel = 'Completed Courses';

    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Completed Courses';

    public static function getEloquentQuery(): Builder
    {
        // Get enrollments where completion >= 90% and no approved certificate exists
        return parent::getEloquentQuery()
            ->where('status', 'active')
            ->whereHas('course', function ($query) {
                $query->where('is_published', true);
            })
            ->with(['user', 'course', 'certificate'])
            ->select('enrollments.*')
            ->selectRaw('(
                SELECT COUNT(*)
                FROM lesson_progress
                WHERE lesson_progress.user_id = enrollments.user_id
                AND lesson_progress.lesson_id IN (
                    SELECT id FROM lessons 
                    WHERE lessons.course_id = enrollments.course_id 
                    AND lessons.is_published = true
                )
                AND lesson_progress.watched_percentage >= 90
            ) as completed_lessons')
            ->selectRaw('(
                SELECT COUNT(*)
                FROM lessons
                WHERE lessons.course_id = enrollments.course_id
                AND lessons.is_published = true
            ) as total_lessons')
            ->havingRaw('completed_lessons >= (total_lessons * 0.9)')
            ->havingRaw('total_lessons > 0');
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

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('course.level')
                    ->label('Level')
                    ->badge()
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_percentage')
                    ->label('Progress')
                    ->getStateUsing(function ($record) {
                        $total = $record->total_lessons ?? 0;
                        $completed = $record->completed_lessons ?? 0;
                        return $total > 0 ? round(($completed / $total) * 100) : 0;
                    })
                    ->badge()
                    ->colors([
                        'success' => fn ($state) => $state >= 90,
                        'warning' => fn ($state) => $state >= 50 && $state < 90,
                        'primary' => fn ($state) => $state > 0 && $state < 50,
                        'gray' => fn ($state) => $state == 0,
                    ])
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lessons_progress')
                    ->label('Lessons')
                    ->getStateUsing(function ($record) {
                        return ($record->completed_lessons ?? 0) . ' / ' . ($record->total_lessons ?? 0);
                    }),

                Tables\Columns\BadgeColumn::make('certificate_status')
                    ->label('Certificate')
                    ->getStateUsing(function ($record) {
                        if (!$record->certificate) {
                            return 'Not Requested';
                        }
                        return ucfirst($record->certificate->status);
                    })
                    ->colors([
                        'success' => 'Approved',
                        'warning' => 'Pending',
                        'danger' => 'Revoked',
                        'gray' => 'Not Requested',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enrolled')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('certificate_status')
                    ->label('Certificate Status')
                    ->options([
                        'none' => 'No Certificate',
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'revoked' => 'Revoked',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!isset($data['value'])) {
                            return $query;
                        }

                        if ($data['value'] === 'none') {
                            return $query->doesntHave('certificate');
                        }

                        return $query->whereHas('certificate', function ($q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('generate_certificate')
                    ->label('Generate Certificate')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$record->certificate || $record->certificate->status === 'pending')
                    ->action(function ($record) {
                        try {
                            DB::beginTransaction();

                            // Create or get certificate
                            $certificate = $record->certificate;
                            if (!$certificate) {
                                $certificate = Certificate::create([
                                    'user_id' => $record->user_id,
                                    'course_id' => $record->course_id,
                                    'enrollment_id' => $record->id,
                                    'status' => 'pending',
                                ]);
                            }

                            // Generate certificate files
                            $action = new GenerateCertificateAction();
                            $action->execute($certificate);

                            // Update status and issue information
                            $certificate->update([
                                'status' => 'approved',
                                'issued_at' => now(),
                                'issued_by' => Auth::id(),
                            ]);

                            // Fire event to send notification
                            event(new CertificateIssued($certificate));

                            DB::commit();

                            Notification::make()
                                ->title('Certificate Generated Successfully')
                                ->body('Certificate has been generated and sent to ' . $record->user->name)
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Certificate Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('view_certificate')
                    ->label('View Certificate')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->certificate && $record->certificate->status === 'approved')
                    ->url(fn ($record) => \App\Filament\Resources\CertificateResource::getUrl('view', ['record' => $record->certificate])),

                Tables\Actions\Action::make('view_student')
                    ->label('View Student')
                    ->icon('heroicon-o-user')
                    ->url(fn ($record) => \App\Filament\Resources\UserResource::getUrl('view', ['record' => $record->user]))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('generate_certificates')
                    ->label('Generate Certificates')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $success = 0;
                        $failed = 0;

                        foreach ($records as $record) {
                            try {
                                DB::beginTransaction();

                                // Create or get certificate
                                $certificate = $record->certificate;
                                if (!$certificate) {
                                    $certificate = Certificate::create([
                                        'user_id' => $record->user_id,
                                        'course_id' => $record->course_id,
                                        'enrollment_id' => $record->id,
                                        'status' => 'pending',
                                    ]);
                                }

                                // Skip if already approved
                                if ($certificate->status === 'approved') {
                                    continue;
                                }

                                // Generate certificate files
                                $action = new GenerateCertificateAction();
                                $action->execute($certificate);

                                // Update status
                                $certificate->update([
                                    'status' => 'approved',
                                    'issued_at' => now(),
                                    'issued_by' => Auth::id(),
                                ]);

                                // Fire event
                                event(new CertificateIssued($certificate));

                                DB::commit();
                                $success++;

                            } catch (\Exception $e) {
                                DB::rollBack();
                                $failed++;
                            }
                        }

                        Notification::make()
                            ->title('Bulk Certificate Generation Complete')
                            ->body("Generated: {$success}, Failed: {$failed}")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompletedCourses::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Count enrollments eligible for certificates but don't have approved certificates
        $count = static::getEloquentQuery()
            ->whereDoesntHave('certificate', function ($query) {
                $query->where('status', 'approved');
            })
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
