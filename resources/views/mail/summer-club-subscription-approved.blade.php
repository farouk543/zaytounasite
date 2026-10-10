<x-mail::message>
# Bonjour {{ $request->parent_name }},

La demande d'abonnement au **Club d'été Zaytouna** pour **{{ $request->student_name }}** a été **acceptée** 🎉

## Récapitulatif

<x-mail::table>
| | |
|:--|:--|
| **Formule** | {{ $request->pack_name }} |
| **Matières** | {{ implode(', ', $enrollment->selected_subjects ?? []) }} |
| **Niveau** | {{ $level }} |
| **Période** | du {{ $enrollment->starts_at?->format('d/m/Y') }} au {{ $enrollment->expires_at?->format('d/m/Y') }} |
| **Montant** | {{ $price }} |
</x-mail::table>

## Accès de l'élève

<x-mail::panel>
@if(!empty($access['platform_url']))
**Adresse :** {{ $access['platform_url'] }}<br>
@endif
**Identifiant :** {{ $access['login'] ?? '—' }}<br>
@if(!empty($access['password']))
**Mot de passe provisoire :** {{ $access['password'] }}
@endif
</x-mail::panel>

@if(!empty($access['platform_url']))
<x-mail::button :url="$access['platform_url']">
Se connecter
</x-mail::button>
@endif

@if(!empty($access['schedule']))
## Planning

{!! nl2br(e($access['schedule'])) !!}
@endif

@if(!empty($access['message']))
## Message de l'équipe

{!! nl2br(e($access['message'])) !!}
@endif

Pour toute question, contactez-nous{{ $contactPhone ? ' au '.$contactPhone : '' }}{{ $contactEmail ? ($contactPhone ? ' ou' : '').' à '.$contactEmail : '' }}.

Merci de votre confiance,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
