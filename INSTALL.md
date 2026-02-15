# Rychlá instalace - Infographic CMS

## ⚡ Rychlé kroky

### 1. Vytvořte databázi
```bash
mysql -u root -p
```
```sql
CREATE DATABASE infographic_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 2. Importujte schéma
```bash
mysql -u root -p infographic_cms < database.sql
```

### 3. Upravte konfiguraci databáze
Editujte soubor `config/db.php`:
```php
'host' => 'localhost',
'dbname' => 'infographic_cms',
'username' => 'root',
'password' => 'VAŠE_HESLO_K_DB',
```

### 4. Nastavte Apache Virtual Host
**DŮLEŽITÉ:** Document Root musí být `/public`!

```apache
<VirtualHost *:80>
    ServerName econvisuals.local
    DocumentRoot /cesta/k/projektu/public

    <Directory /cesta/k/projektu/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Error log
    ErrorLog ${APACHE_LOG_DIR}/econvisuals-error.log
    CustomLog ${APACHE_LOG_DIR}/econvisuals-access.log combined
</VirtualHost>
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### 5. Nastavte oprávnění
```bash
cd /cesta/k/projektu
chmod 755 -R .
chmod 775 -R uploads/
chown -R www-data:www-data uploads/
```

### 6. Vytvořte administrátora
```bash
php seed-admin.php
```
Zadejte:
- Uživatelské jméno: `admin`
- Heslo: `VašeSilnéHeslo123!@#`

**⚠️ DŮLEŽITÉ:** Ihned po vytvoření SMAŽTE soubor `seed-admin.php`!
```bash
rm seed-admin.php
```

### 7. Přejmenujte admin složku (doporučeno)
```bash
mv jsilepsi tajnaheslo123
```
Teď je admin na: `https://econvisuals.local/tajnaheslo123`

### 8. Přihlaste se
Otevřete prohlížeč:
```
http://econvisuals.local/jsilepsi   (nebo vaše nová URL)
```

Přihlaste se s údaji z kroku 6.

---

## 🔧 Pro localhost (XAMPP/WAMP)

### Windows (XAMPP)
1. Zkopírujte projekt do `C:\xampp\htdocs\econvisuals`
2. Upravte `config/db.php`
3. Importujte databázi přes phpMyAdmin
4. Spusťte: `php seed-admin.php`
5. Otevřete: `http://localhost/econvisuals/public`

**Poznámka:** Na localhost musíte přistupovat přes `/public`, protože XAMPP nemá virtualhost.

Lepší řešení - vytvořte virtualhost v XAMPP:
Editujte `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/econvisuals/public"
    ServerName econvisuals.local
</VirtualHost>
```

A přidejte do `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 econvisuals.local
```

---

## ✅ Kontrolní seznam

- [ ] Database vytvořena a importována
- [ ] config/db.php nakonfigurován
- [ ] Document Root nastaven na /public
- [ ] Administrátor vytvořen
- [ ] seed-admin.php SMAZÁN
- [ ] Admin složka přejmenována
- [ ] Oprávnění nastavena (755/775)
- [ ] Apache restartován
- [ ] Úspěšné přihlášení do adminu

---

## 🎉 Hotovo!

Nyní můžete:
1. Přihlásit se do administrace
2. Vytvořit první kategorii (nebo použít předvytvořené)
3. Nahrát infografiku
4. Publikovat!

---

## 🆘 Řešení problémů

### Chyba: "Database connection error"
- Zkontrolujte `config/db.php`
- Ověřte, že databáze existuje
- Zkontrolujte uživatelské jméno a heslo

### Chyba: "404 Not Found" nebo "Internal Server Error"
- Ověřte, že Document Root je `/public`
- Zkontrolujte, že mod_rewrite je zapnutý: `sudo a2enmod rewrite`
- Zkontrolujte `.htaccess` soubory

### Chyba při uploadu obrázku
- Zkontrolujte oprávnění složky `uploads/`: `chmod 775 -R uploads/`
- Ověřte, že PHP GD extension je nainstalována: `php -m | grep gd`
- Zkontrolujte PHP limity v `php.ini`:
  ```
  upload_max_filesize = 10M
  post_max_size = 12M
  ```

### Admin stránka se nezobrazuje (404)
- Pokud jste přejmenovali složku `/jsilepsi`, použijte novou URL
- Zkontrolujte, že složka existuje a obsahuje `index.php`

---

## 📚 Další kroky

Po úspěšné instalaci přečtěte `README.md` pro:
- Bezpečnostní doporučení
- Produkční nastavení
- Customizaci designu
- API dokumentaci
