# 🧪 Google Analytics Consent Test Plan

## KRITICKÝ TEST - Musí projít 100%

Tento test ověří, že **NEZAHAJUJEME tracking bez souhlasu uživatele**.

---

## Test 1: Bez souhlasu - ŽÁDNÝ tracking ❌

### Kroky:
1. **Otevři anonymní okno** (Ctrl+Shift+N v Chrome)
2. **Otevři DevTools** (F12)
3. **Jdi na Network tab**
4. **Zadej filter**: `collect` (do search boxu)
5. **Otevři stránku**: https://www.econvisuals.com/
6. **NEKLIKEJ** na žádné tlačítko v cookie banneru

### ✅ PASS kritéria:
- Vidíš **0 requestů** obsahujících `/collect` nebo `/g/collect`
- Vidíš request na `gtag/js?id=G-TRG4YVSB6S` (to je OK - jen načítá script)
- Cookie banner je **viditelný**

### ❌ FAIL kritéria:
- Vidíš **JAKÝKOLI** request na `/collect` nebo `/g/collect`
- **→ To znamená tracking BEZ souhlasu = PROBLÉM!**

---

## Test 2: Po "Accept All" - Tracking funguje ✅

### Kroky:
1. Pokračuj z Test 1 (nebo otevři nové anonymní okno)
2. DevTools Network tab je stále otevřený, filter `collect` je aktivní
3. **Klikni na "Accept All"** v cookie banneru
4. **Počkej 2 sekundy**

### ✅ PASS kritéria:
- Vidíš **1+ requestů** na `/g/collect` nebo `google-analytics.com/g/collect`
- Request obsahuje parametr `en=page_view`
- Cookie banner **zmizel**

### ❌ FAIL kritéria:
- **ŽÁDNÝ** request na `/collect` se neobjevil
- **→ Tracking nefunguje ani po souhlasu = PROBLÉM!**

---

## Test 3: Po "Decline All" - ŽÁDNÝ tracking ❌

### Kroky:
1. **Nové anonymní okno** (Ctrl+Shift+N)
2. DevTools → Network → Filter: `collect`
3. Otevři: https://www.econvisuals.com/
4. **Klikni na "Decline All"**
5. **Naviguj na jinou stránku** (např. klikni na kategorii Economy)
6. **Zavři a znovu otevři** https://www.econvisuals.com/

### ✅ PASS kritéria:
- **0 requestů** na `/collect` - ani po navigaci, ani po reloadu
- Cookie banner se **NEZOBRAZUJE** (protože máš uložený decline)

### ❌ FAIL kritéria:
- Vidíš request na `/collect`
- **→ Tracking i po odmítnutí = PROBLÉM!**

---

## Test 4: Console Check - Consent State

### Kroky:
1. Otevři https://www.econvisuals.com/ (bez cookies)
2. Otevři Console (F12 → Console tab)
3. Zadej: `window.dataLayer`
4. Klikni Enter

### ✅ PASS kritéria:
```javascript
[
  ["consent", "default", {
    analytics_storage: "denied",  // ← Musí být "denied"
    ad_storage: "denied",
    ad_user_data: "denied",
    ad_personalization: "denied"
  }],
  // ... další položky
]
```

### Po kliknutí "Accept All":
Znovu zadej `window.dataLayer` a měl bys vidět:
```javascript
["consent", "update", {
  analytics_storage: "granted",  // ← Změněno na "granted"
  ...
}]
```

---

## 🎯 Výsledek testu

### ✅ Vše v pořádku pokud:
- Test 1: ❌ ŽÁDNÉ collect requesty před souhlasem
- Test 2: ✅ Collect requesty PO "Accept All"
- Test 3: ❌ ŽÁDNÉ collect requesty po "Decline All"
- Test 4: ✅ Consent správně mění z "denied" na "granted"

### ❌ KRITICKÝ PROBLÉM pokud:
- Test 1 nebo Test 3 zobrazí `/collect` requesty
- **→ Musíme OKAMŽITĚ opravit, jinak porušujeme GDPR!**

---

## Quick Test URL

Diagnostická stránka:
**https://www.econvisuals.com/check-consent.php**

---

## Technické detaily implementace

### Jak to funguje:
1. **Default consent = denied** (řádek 47-53 v header.php)
2. **send_page_view = false** (řádek 57 - zakázán automatický pageview)
3. **Pageview se pošle JEN po Accept** (řádek 155-161 - manuální page_view event)

### Klíčový kód:
```javascript
// Default: DENIED
gtag('consent', 'default', {
  'analytics_storage': 'denied'  // ← Žádný tracking
});

// Config BEZ automatického pageview
gtag('config', 'G-TRG4YVSB6S', {
  'send_page_view': false  // ← Kritické!
});

// Pageview JEN po Accept
if (analyticsAllowed) {
  gtag('event', 'page_view');  // ← Jen když user akceptuje
}
```

---

**Datum testu:** 2026-02-15
**Verze:** Commit 5cf41f8
