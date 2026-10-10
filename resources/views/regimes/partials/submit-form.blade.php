{{-- Formulaire final d'une demande régime : coordonnées du parent + confirmation.
     Paramètre : $slug (tunisia, france, qatar, saudi, quran) --}}
@php
  $u = auth()->user();
  $fieldStyle = 'width:100%; padding:10px 12px; border:1px solid rgba(15,23,42,.15); border-radius:10px; font:inherit; background:#fff;';
  $labelStyle = 'display:block; font-weight:700; font-size:13px; margin-bottom:4px; color:#0f172a;';
@endphp

<form method="POST" action="{{ route("regimes.{$slug}.submit") }}" style="flex-basis:100%; width:100%; margin-top:6px;">
  @csrf

  <div class="lux-card" style="padding:16px; background:rgba(15,23,42,.03);">
    <div style="font-weight:900; margin-bottom:4px;">Vos coordonnées</div>
    <div class="za-muted" style="font-size:13px; margin-bottom:12px;">
      Elles nous permettent de vous contacter et de vous envoyer les accès à la plateforme de cours.
    </div>

    @if($errors->any())
      <div style="background:#fef2f2; color:#991b1b; border:1px solid #fecaca; border-radius:10px; padding:10px 12px; margin-bottom:12px; font-size:14px;">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:12px;">
      <div>
        <label for="parent_name" style="{{ $labelStyle }}">Nom du parent *</label>
        <input id="parent_name" name="parent_name" type="text" required maxlength="255"
               value="{{ old('parent_name', $u?->name) }}" style="{{ $fieldStyle }}">
      </div>
      <div>
        <label for="student_name" style="{{ $labelStyle }}">Nom de l'élève *</label>
        <input id="student_name" name="student_name" type="text" required maxlength="255"
               value="{{ old('student_name') }}" style="{{ $fieldStyle }}">
      </div>
      <div>
        <label for="phone" style="{{ $labelStyle }}">Téléphone / WhatsApp *</label>
        <input id="phone" name="phone" type="tel" required maxlength="40"
               value="{{ old('phone') }}" placeholder="+216 …" style="{{ $fieldStyle }}">
      </div>
      <div>
        <label for="contact_email" style="{{ $labelStyle }}">E-mail *</label>
        <input id="contact_email" name="contact_email" type="email" required maxlength="255"
               value="{{ old('contact_email', $u?->email) }}" style="{{ $fieldStyle }}">
      </div>
    </div>

    <div style="margin-top:12px;">
      <label for="customer_notes" style="{{ $labelStyle }}">Disponibilités / remarques (facultatif)</label>
      <textarea id="customer_notes" name="customer_notes" rows="3" maxlength="2000"
                placeholder="Ex. : mercredi après-midi et samedi matin"
                style="{{ $fieldStyle }}">{{ old('customer_notes') }}</textarea>
    </div>

    <div style="margin-top:14px;">
      <button type="submit" class="btn-primary" onclick="if(this.form.checkValidity()){this.disabled=true;this.textContent='Envoi en cours…';this.form.submit();}">
        ✅ Confirmer ma demande
      </button>
    </div>
  </div>
</form>
