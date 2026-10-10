@props(['url'])
@php
  // URL absolue obligatoire dans un e-mail : MAIL_LOGO_URL en priorité, sinon le logo du site
  $logoUrl = config('zaytouna.mail_logo_url') ?: asset('images/logo_final.png');
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" width="90" height="90" style="width: 90px; height: 90px; max-height: 90px; border: none;">
</a>
</td>
</tr>
