# Infographic CMS

Minimalistický, bezpečný a rychlý CMS zaměřený na publikaci čtvercových infografik.

## 🚀 Vlastnosti

- **Bezpečnost**: Argon2id hashing, CSRF ochrana, rate limiting, honeypot
- **Rychlost**: Vanilla PHP, CSS Grid, WebP optimalizace, lazy loading
- **SEO**: Open Graph meta tagy, strukturované URL, meta descriptions
- **Moderní design**: Mobile-first, CSS Grid, aspect-ratio 1:1, dark mode
- **Skrytá administrace**: Nepředvídatelná URL s X-Robots-Tag

## 📋 Požadavky

- PHP 8.2+ (s GD extension pro zpracování obrázků)
- MariaDB / MySQL 8.0+
- Apache s mod_rewrite (nebo Nginx)
- HTTPS (doporučeno)

## 🔧 Instalace

### 1. Naklonujte projekt
```bash
git clone <repository-url>
cd econvisuals.com
```

### 2. Vytvořte databázi
```sql
CREATE DATABASE infographic_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importujte schéma
```bash
mysql -u root -p infographic_cms < database.sql
```

### 4. Nakonfigurujte databázi
Upravte soubor `config/db.php`:
```php
return [
    'host' => 'localhost',
    'dbname' => 'infographic_cms',
    'username' => 'root',
    'password' => 'vaše_heslo',
    // ...
];
```

### 5. Nastavte Document Root
Váš webový server musí mít **Document Root** nastaven na složku `/public`.

**Apache virtualhost příklad:**
```apache
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/econvisuals.com/public

    <Directory /var/www/econvisuals.com/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### 6. Vytvořte prvního administrátora
```bash
php seed-admin.php admin VašeSuperSilnéHeslo123
```

**DŮLEŽITÉ:** Po vytvoření administrátora **SMAŽTE** soubor `seed-admin.php`!

### 7. Nastavte oprávnění
```bash
chmod 755 -R .
chmod 775 -R uploads/
```

## 🔐 Přihlášení do administrace

URL: `https://example.com/jsilepsi`

**Bezpečnostní doporučení:**
- Po instalaci změňte URL `/jsilepsi` na něco unikátního
- Přejmenujte složku `/jsilepsi` na vlastní název
- Použijte silné heslo (min. 16 znaků, čísla, symboly)

## 📁 Struktura projektu

```
/
├── config/             # Konfigurace databáze
├── src/                # PHP třídy a logika
│   ├── templates/      # HTML šablony
│   ├── Auth.php
│   ├── Database.php
│   ├── Router.php
│   ├── ImageHandler.php
│   └── ...
├── public/             # Document Root (veřejný přístup)
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   ├── index.php
│   └── .htaccess
├── uploads/            # Nahrané obrázky
│   └── infographics/
├── jsilepsi/           # Administrace (přejmenujte!)
│   ├── index.php
│   ├── dashboard.php
│   └── editor.php
├── database.sql        # DB schéma
└── README.md
```

## 🎨 Frontend

### Šablony
- `homepage.php` - Grid infografik
- `post-detail.php` - Detail článku
- `category.php` - Filtr podle kategorie
- `tag.php` - Filtr podle tagu

### CSS Vlastnosti
- **CSS Grid** pro responzivní layout
- **aspect-ratio: 1/1** pro dokonalé čtverce
- **Mobile-first** přístup
- **Dark mode** (@media prefers-color-scheme)
- **Lazy loading** obrázků
- **srcset** pro responzivní obrázky

## 🔒 Bezpečnost

### Implementované mechanismy:
- ✅ Argon2id password hashing
- ✅ CSRF token ochrana
- ✅ Rate limiting (exponential backoff)
- ✅ Honeypot pro boty
- ✅ XSS ochrana (htmlspecialchars)
- ✅ SQL Injection prevence (PDO prepared statements)
- ✅ Session fixation prevence
- ✅ Session hijacking prevence
- ✅ Validace MIME typů (finfo)
- ✅ Bezpečné přejmenování souborů
- ✅ .htaccess v /uploads (no PHP execution)
- ✅ X-Robots-Tag pro admin
- ✅ Security headers (X-Frame-Options, CSP, atd.)

### Doporučení pro produkci:
1. Zapnout HTTPS a nastavit `session.cookie_secure = 1`
2. Změnit admin URL na unikátní
3. Pravidelně aktualizovat PHP
4. Monitorovat error logy
5. Zálohovat databázi

## 📊 Optimalizace

### Obrázky
- Automatická konverze do WebP
- Resize na max. 1200px
- Komprese 85% kvalita
- Generování více velikostí (600px, 800px, 1200px)
- Odstranění EXIF metadat

### Performance
- Lazy loading obrázků
- Browser caching (.htaccess)
- GZIP komprese
- Minimální JavaScript
- CSS Grid místo JavaScriptu

## 🛠️ Vývoj

### Přidání nové kategorie
```sql
INSERT INTO categories (name, slug) VALUES ('Nová kategorie', 'nova-kategorie');
```

### Customizace CSS
Upravte soubor `/public/assets/css/main.css`

CSS proměnné jsou definovány v `:root`:
```css
:root {
    --color-primary: #2563eb;
    --color-background: #ffffff;
    /* ... */
}
```

## 📝 Licence

MIT License - Použijte podle potřeby

## 🐛 Reporting Issues

Pro hlášení chyb nebo návrhy na vylepšení vytvořte issue.

## ✨ Credits

Vytvořeno podle návrhového plánu pro ultrarychlý a bezpečný mikro-CMS.
