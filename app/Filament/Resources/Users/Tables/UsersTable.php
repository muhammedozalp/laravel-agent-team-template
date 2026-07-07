<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Notifications\AccountApproved;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('approved_at')
                    ->label('Approved')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => ! $record->isApproved())
                    ->action(function (User $record): void {
                        $record->forceFill(['approved_at' => now()])->save();
                        $record->notify(new AccountApproved);

                        Notification::make()
                            ->title(__(':name approved', ['name' => $record->name]))
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    // Locking yourself out is never the intent.
                    ->hidden(fn (User $record): bool => $record->is(auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
