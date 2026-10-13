### Largebackup für Moodle

Largebackup ist ein lokales Moodle-Plugin zum Hochladen großer Moodle-Kurssicherungen (`.mbz`). Die Dateien werden in mehreren Teilen (Chunks) übertragen. Nach dem Upload öffnet das Plugin den Moodle-Wiederherstellungsassistenten.

### Funktionen

- Chunk-basierter Upload großer Kurssicherungen
- Unterstützung von Moodle-Sicherungsdateien mit der Endung `.mbz`
- Konfigurierbares Upload-Limit
- Prüfung der Dateiendung
- Protokollierung der Upload-Vorgänge
- Übergabe an den Moodle-Wiederherstellungsassistenten

Die Wiederherstellung selbst erfolgt über die Moodle-Standardfunktion, nicht durch das Plugin.

### Voraussetzungen

- Moodle 4.2 oder neuer
- Ausreichend freier Speicher im Moodle-Datenverzeichnis
- PHP-, Webserver- und Proxy-Limits, die die einzelnen Upload-Chunks zulassen
- Passende Berechtigungen im Kurs, aus dem das Tool gestartet wird

### Installation

1. Das Plugin-Verzeichnis nach `local/largebackup` im Moodle-Hauptverzeichnis kopieren.
2. Als Administrator die Seite **Website-Administration → Mitteilungen** öffnen und die Installation durchführen.
3. Alternativ das Upgrade aus dem Moodle-Hauptverzeichnis über die Kommandozeile starten:

   ```bash
   php admin/cli/upgrade.php --non-interactive
   ```

4. Die Plugin-Einstellungen und Berechtigungen prüfen.

**Mehrere Moodle-Instanzen:** Wenn mehrere Instanzen dieselben Plugin-Dateien verwenden, aber getrennte Datenbanken besitzen, muss das Upgrade für jede Instanz ausgeführt werden. Dabei muss die jeweils richtige Instanzkonfiguration geladen werden.

### Verwendung

1. Den betreffenden Kurs öffnen.
2. Largebackup über den im Kurs angebotenen Link aufrufen.
3. Eine Moodle-Kurssicherung mit der Endung `.mbz` auswählen.
4. Auf **Upload starten** klicken und den Upload abwarten.
5. Im anschließend geöffneten Moodle-Wiederherstellungsassistenten das Ziel und die Wiederherstellungsoptionen auswählen.
6. Die Wiederherstellung abschließen.

Welche Wiederherstellungsziele und Optionen verfügbar sind, hängt von den Moodle-Berechtigungen des angemeldeten Nutzers ab.

### Berechtigungen

Folgende Fähigkeiten sind besonders relevant:

| Fähigkeit | Zweck |
|---|---|
| `local/largebackup:uploadbackup` | Das Upload-Tool verwenden |
| `moodle/restore:restorecourse` | Den Moodle-Wiederherstellungsassistenten für Kurssicherungen verwenden |
| `moodle/restore:configure` | Wiederherstellungsoptionen konfigurieren |
| `moodle/course:create` | Einen neuen Kurs anlegen, falls dies als Wiederherstellungsziel gewählt wird |
| `moodle/restore:userinfo` | Nutzerdaten wiederherstellen, sofern ausdrücklich erlaubt |

Für den Upload und den anschließenden Einstieg in die Wiederherstellung müssen insbesondere `local/largebackup:uploadbackup` und `moodle/restore:restorecourse` im Ausgangskurs wirksam sein. Weitere Rechte hängen vom gewählten Wiederherstellungsablauf ab.

#### Rechte einrichten

1. **Website-Administration → Nutzer/innen → Berechtigungen → Rollen definieren** öffnen.
2. Die gewünschte Rolle bearbeiten.
3. Die benötigten Fähigkeiten suchen und gezielt auf **Erlauben** setzen.
4. Änderungen speichern.
5. Im betreffenden Kurs über **Rechte für Nutzerkonto prüfen** kontrollieren, ob die Rechte tatsächlich wirksam sind.

Alternativ können Rechte gezielt über Rollenüberschreibungen im betreffenden Kursbereich oder Kurs vergeben werden.

#### Trainer/innen und Kursersteller/innen

Eine Trainer-Rolle im Kurs gilt nicht automatisch in anderen Kursen oder im übergeordneten Kursbereich. Ebenso bedeutet die Rolle Kursersteller/in nicht automatisch, dass alle Wiederherstellungsrechte vorhanden sind.

Bei einer Wiederherstellung als neuer Kurs müssen die erforderlichen Rechte auch im Zielkursbereich vorhanden sein.

**Wichtig:** `moodle/restore:userinfo` nicht pauschal freigeben. Dieses Recht betrifft die Wiederherstellung von Nutzerdaten und ist für den Datei-Upload selbst nicht erforderlich.

### Upload-Limit

Die maximale Gesamtgröße einer Sicherungsdatei wird über die Plugin-Einstellung `maxfilesize` festgelegt.

- Einheit: MB
- Beispiel: `512` für ein Upload-Limit von 512 MB

Ist eine Einstellungsseite für das Plugin eingerichtet, kann der Wert unter **Website-Administration → Plugins → Lokale Plugins → Largebackup** angepasst werden. Die genaue Bezeichnung des Menüeintrags hängt von der Plugin-Version ab.

Das Gesamtdatei-Limit des Plugins und die serverseitigen Limits sind getrennt zu betrachten:

- Das Plugin-Limit begrenzt die gesamte Sicherungsdatei.
- `upload_max_filesize` muss mindestens die Größe eines einzelnen Chunks zulassen.
- `post_max_size` muss den gesamten Chunk-Request einschließlich zusätzlicher Formulardaten zulassen.
- Webserver und vorgeschaltete Proxys können weitere Größen- und Zeitlimits setzen.

Durch den Chunk-Upload muss die Gesamtgröße der Sicherung nicht innerhalb des PHP-Limits für eine einzelne hochgeladene Datei liegen.

### Updates und Datenbank

Das Plugin verwendet die Tabelle `local_largebackup_logs`. Moodle ergänzt den konfigurierten Tabellenpräfix, beispielsweise `mdl_local_largebackup_logs`.

- Neuinstallationen verwenden `db/install.xml`.
- Updates bestehender Installationen verwenden `db/upgrade.php`.
- Eine höhere Versionsnummer in `version.php` löst bei einem Moodle-Upgrade die entsprechenden neuen Upgrade-Schritte aus.

Nach einer Aktualisierung das Moodle-Upgrade ausführen:

```bash
php admin/cli/upgrade.php --non-interactive
```

Bei Bedarf anschließend die Caches leeren:

```bash
php admin/cli/purge_caches.php
```

Ein gespeicherter Plugin-Versionswert allein garantiert nicht, dass die Tabelle vorhanden ist. Fehlt sie trotz aktuellem Versionsstand, ist ein gezielter Reparatur-Upgrade-Schritt erforderlich. Das erneute Ausführen derselben Plugin-Version führt bereits abgeschlossene Upgrade-Schritte nicht nochmals aus.

### Fehlerbehebung

#### „Sie haben aktuell nicht das Recht, dies zu tun (Kurse wiederherstellen)“

- `moodle/restore:restorecourse` im betreffenden Kurs prüfen.
- Kontrollieren, ob der Aufruf von `backup/restore.php` die Kontext-ID des Ausgangskurses enthält.
- Nicht die Nutzer-Kontext-ID als Berechtigungskontext an die Wiederherstellungsseite übergeben.

#### Fehlende Tabelle `local_largebackup_logs`

- Prüfen, ob das Upgrade für die richtige Moodle-Instanz ausgeführt wurde.
- Tabellenbestand und Plugin-Versionsstand in deren Datenbank kontrollieren.
- Bei bereits gespeichertem aktuellem Versionsstand einen neuen Reparatur-Upgrade-Schritt verwenden.

#### Upload-Limit überschritten

- Plugin-Einstellung `maxfilesize` prüfen.
- Größe der Upload-Chunks mit den PHP-, Webserver- und Proxy-Limits vergleichen.
- Freien Speicherplatz im Moodle-Datenverzeichnis prüfen.

#### „Fehler: Not Found“

Diese Meldung allein benennt die Ursache nicht eindeutig.

- Den fehlgeschlagenen Request im Netzwerkbereich der Browser-Entwicklertools öffnen.
- HTTP-Status, Antwortinhalt und mögliche Weiterleitungen prüfen.
- PHP-, Webserver- und Proxy-Logs zum gleichen Zeitpunkt auswerten.
- Prüfen, ob die Plugin-Datenbanktabelle vorhanden ist.

### Sicherheit und Protokollierung

- Nur die tatsächlich benötigten Rechte vergeben.
- Änderungen an Rollen und Plugin-Versionen zunächst auf einer Testinstanz prüfen.
- Debug-Protokollierung im Produktivbetrieb nur bei Bedarf aktivieren.
- Debug-Protokolle können Nutzerinformationen und interne Dateipfade enthalten und müssen entsprechend geschützt werden.
