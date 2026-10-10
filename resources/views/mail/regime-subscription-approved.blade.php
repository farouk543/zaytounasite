<x-mail::message>
# Bonjour {{ $parentName }},

Bonne nouvelle : votre demande d'abonnement aux **cours live Zaytouna Academy** a été **acceptée** 🎉

Le compte de l'élève a été créé sur notre plateforme de cours. Vous trouverez ci-dessous toutes les informations nécessaires pour commencer.

## Vos accès à la plateforme

<x-mail::panel>
@if(!empty($access['platform_url']))
**Adresse de la plateforme :** {{ $access['platform_url'] }}<br>
@endif
**Identifiant :** {{ $access['login'] ?? '—' }}<br>
@if(!empty($access['password']))
**Mot de passe provisoire :** {{ $access['password'] }}
@endif
</x-mail::panel>

@if(!empty($access['password']))
Pour votre sécurité, pensez à changer ce mot de passe lors de votre première connexion.
@endif

@if(!empty($access['platform_url']))
<x-mail::button :url="$access['platform_url']">
Accéder à la plateforme
</x-mail::button>
@endif

## Récapitulatif de l'abonnement

<x-mail::table>
| | |
|:--|:--|
| **N° de demande** | #{{ $order->id }} |
| **Programme** | {{ $regime }} |
| **Formule** | {{ $pack }}{{ $plan ? ' — '.$plan : '' }} |
@if($order->student_name)
| **Élève** | {{ $order->student_name }} |
@endif
| **Montant** | {{ $total }} |
@if(!empty($access['start_date']))
| **Début des cours** | {{ \Illuminate\Support\Carbon::parse($access['start_date'])->format('d/m/Y') }} |
@endif
@if(!empty($access['teacher']))
| **Professeur** | {{ $access['teacher'] }} |
@endif
</x-mail::table>

@if(count($lines))
**Matières :**

@foreach($lines as $line)
- {{ $line }}
@endforeach
@endif

@if(!empty($access['schedule']))
## Planning des séances

{!! nl2br(e($access['schedule'])) !!}
@endif

@if(!empty($access['message']))
## Message de l'équipe

{!! nl2br(e($access['message'])) !!}
@endif

## Besoin d'aide ?

Notre équipe reste à votre disposition{{ $contactPhone ? ' au '.$contactPhone : '' }}{{ $contactEmail ? ($contactPhone ? ' ou' : '').' par e-mail à '.$contactEmail : '' }}.

Merci de votre confiance,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
