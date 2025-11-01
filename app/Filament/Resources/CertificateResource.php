<?php

namespace App\Filament\Resources;

use App\Actions\Certificate\GenerateCertificateAction;
use App\Events\CertificateIssued;
use App\Filament\Resources\CertificateResource\Pages;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Analytics';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Certificate Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Student')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Select::make('course_id')
                            ->label('Course')
                            ->relationship('course', 'title')
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Forms\Components\Select::make('enrollment_id')
                            ->label('Enrollment')
                            ->relationship('enrollment', 'id')
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record !== null),
                    ])->columns(3),

                Forms\Components\Section::make('Certificate Details')
                    ->schema([
                        Forms\Components\TextInput::make('certificate_number')
                            ->label('Certificate Number')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('verification_hash')
                            ->label('Verification Hash')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'revoked' => 'Revoked',
                            ])
                            ->required()
                            ->disabled(),
                    ])->columns(3),

                Forms\Components\Section::make('Issuance Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('issued_at')
                            ->label('Issued At')
                            ->disabled(),

                        Forms\Components\Select::make('issued_by')
                            ->label('Issued By')
                            ->relationship('issuer', 'name')
                            ->disabled(),
                    ])->columns(2)
                    ->visible(fn ($record) => $record && $record->issued_at),

                Forms\Components\Section::make('Revocation Information')
                    ->schema([
                        Forms\Components\Textarea::make('revocation_reason')
                            ->label('Revocation Reason')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\DateTimePicker::make('revoked_at')
                            ->label('Revoked At')
                            ->disabled(),

                        Forms\Components\Select::make('revoked_by')
                            ->label('Revoked By')
                            ->relationship('revoker', 'name')
                            ->disabled(),
                    ])->columns(3)
                    ->visible(fn ($record) => $record && $record->revoked_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificate_number')
                    ->label('Certificate #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Certificate number copied!')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('course.title')
                    ->label('Course')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'revoked',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued Date')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Not issued'),

                Tables\Columns\TextColumn::make('issuer.name')
                    ->label('Issued By')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'revoked' => 'Revoked',
                    ]),

                Tables\Filters\SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Certificate $record) => $record->status === 'pending')
                    ->action(function (Certificate $record) {
                        try {
                            $action = new GenerateCertificateAction();

                            // Generate certificate files
                            $action->execute($record);

                            // Update status and issue information
                            $record->update([
                                'status' => 'approved',
                                'issued_at' => now(),
                                'issued_by' => Auth::id(),
                            ]);

                            // Fire event to send notification
                            event(new CertificateIssued($record));

                            Notification::make()
                                ->title('Certificate Approved')
                                ->body('Certificate has been generated and sent to the student.')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Certificate Generation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Certificate $record) => $record->status === 'approved')
                    ->action(function (Certificate $record) {
                        try {
                            $action = new GenerateCertificateAction();
                            $action->execute($record);

                            Notification::make()
                                ->title('Certificate Regenerated')
                                ->body('Certificate files have been regenerated successfully.')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Regeneration Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('revoke')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Certificate $record) => $record->status === 'approved')
                    ->form([
                        Forms\Components\Textarea::make('revocation_reason')
                            ->label('Reason for Revocation')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Certificate $record, array $data) {
                        $record->update([
                            'status' => 'revoked',
                            'revocation_reason' => $data['revocation_reason'],
                            'revoked_at' => now(),
                            'revoked_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Certificate Revoked')
                            ->body('Certificate has been revoked successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('download_pdf')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (Certificate $record) => $record->status === 'approved' && $record->pdf_path)
                    ->url(fn (Certificate $record) => route('certificates.download', [
                        'certificate' => $record->id,
                        'format' => 'pdf'
                    ]))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->url(fn (Certificate $record) => $record->verification_url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCertificates::route('/'),
            'view' => Pages\ViewCertificate::route('/{record}'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
