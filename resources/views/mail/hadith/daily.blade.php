<x-mail::message>
# Günün Hadisi

**Hadis No:** {{ $hadith['hadith_id'] }}

---

## العربية

<div style="direction: rtl; text-align: right; font-size: 18px; line-height: 2;">
{!! $hadith['arabic'] !!}
</div>

---

## Türkçe

{!! $hadith['turkish'] !!}

---

Hayırlı günler,
</x-mail::message>

