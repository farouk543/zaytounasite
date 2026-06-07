@extends('layouts.app')

@section('title', 'Club d’été - Accès en attente')

@section('content')
<div class="student-club-page">
    <section class="student-club-locked">
        <div class="za-container">
            <div class="student-club-lockedBox">
                <span class="student-club-lockIcon" aria-hidden="true">
                    @svg('heroicon-o-lock-closed')
                </span>
                <h1>Votre abonnement doit être confirmé.</h1>
                <p>
                    {{ $message ?? 'Dès validation, le catalogue pédagogique du Club d’été sera disponible ici avec les exercices, quiz et corrections.' }}
                </p>
                <a href="{{ route('club.ete') }}" class="student-club-primaryAction">Retour au Club d’été</a>
            </div>
        </div>
    </section>
</div>
@endsection
