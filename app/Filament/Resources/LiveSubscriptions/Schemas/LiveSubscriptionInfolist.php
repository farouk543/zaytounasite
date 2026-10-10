<?php

namespace App\Filament\Resources\LiveSubscriptions\Schemas;

use App\Models\Order;
use App\Support\RegimeSubscription;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LiveSubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Abonnement demandé')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('regime')
                            ->label('Programme')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn (?string $state) => RegimeSubscription::regimeLabel($state)),
                        TextEntry::make('pack')
                            ->label('Pack')
                            ->badge()
                            ->formatStateUsing(fn (?string $state) => RegimeSubscription::packLabel($state)),
                        TextEntry::make('plan')
                            ->label('Formule')
                            ->formatStateUsing(fn (?string $state) => RegimeSubscription::planLabel($state))
                            ->placeholder('Mensuel'),
                        TextEntry::make('total_cents')
                            ->label('Montant')
                            ->formatStateUsing(fn ($state, Order $record) => RegimeSubscription::totalLabel($record))
                            ->weight('bold'),
                        TextEntry::make('details')
                            ->label('Matière(s)')
                            ->state(fn (Order $record) => RegimeSubscription::detailLines($record))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('customer_notes')
                            ->label('Disponibilités / remarques du parent')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Parent & élève')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent_name')
                            ->label('Parent')
                            ->state(fn (Order $record) => $record->parent_name ?: $record->user?->name),
                        TextEntry::make('student_name')
                            ->label('Élève')
                            ->placeholder('—'),
                        TextEntry::make('phone')
                            ->label('Téléphone')
                            ->placeholder('—')
                            ->copyable()
                            ->url(fn (Order $record) => $record->phone ? 'tel:' . preg_replace('/[^\d+]/', '', $record->phone) : null),
                        TextEntry::make('contact_email')
                            ->label('E-mail')
                            ->state(fn (Order $record) => $record->parentEmail())
                            ->copyable(),
                        TextEntry::make('user.email')
                            ->label('Compte sur le site')
                            ->url(fn (Order $record) => $record->user_id ? url('/admin/users/' . $record->user_id . '/edit') : null),
                        TextEntry::make('created_at')
                            ->label('Demande reçue le')
                            ->dateTime('d/m/Y H:i'),
                    ]),

                Section::make('Suivi')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (?string $state) => RegimeSubscription::statusLabel($state))
                            ->color(fn (?string $state) => match ($state) {
                                'pending_manual' => 'warning',
                                'paid'           => 'success',
                                default          => 'danger',
                            }),
                        TextEntry::make('platform_username')
                            ->label('Identifiant plateforme')
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('access_sent_at')
                            ->label('Accès envoyés le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Pas encore envoyés'),
                        TextEntry::make('admin_notes')
                            ->label('Notes internes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
