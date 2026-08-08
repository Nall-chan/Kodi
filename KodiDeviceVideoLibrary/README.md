[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi Video Datenbank (KodiDeviceVideoLibrary)  <!-- omit in toc -->

Ermöglicht das Auslesen, Scannen, Bereinigen und Exportieren der Video-Datenbank von Kodi in IP-Symcon.

## Inhaltsverzeichnis  <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen](#5-statusvariablen)
- [6. Visualisierung](#6-visualisierung)
  - [Kachel Visualisierung](#kachel-visualisierung)
  - [WebFront Visualisierung](#webfront-visualisierung)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Aktionen](#8-aktionen)
- [9. Anhang](#9-anhang)
  - [1. Changelog](#1-changelog)
  - [2. Spenden](#2-spenden)
- [10. Lizenz](#10-lizenz)

## 1. Funktionsumfang

- Zugriff auf die Kodi-Video-Datenbank.
- Auslesen von Filmen, Serien, Episoden, Film-Sets und Musikvideos.
- Ausführen von Scannen und Bereinigen der Datenbank.
- Exportieren der Video-Datenbank.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Eingerichtete Videobibliothek in Kodi

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

Eine einfache Einrichtung ist über den Konfigurator [Kodi Konfigurator](../KodiConfigurator/README.md) möglich.  
Bei der manuellen Einrichtung ist die Instanz im Dialog `Instanz hinzufügen` unter dem Hersteller `Kodi` zu finden.

**Konfigurationsseite:**

| Name | Eigenschaft | Typ | Standardwert | Funktion |
| :--- | :--- | :---: | :--- | :--- |
| Zeige Scan-Status | showScan | bool | true | Statusvariable für DB-Scan anzeigen |
| Zeige Clean-Status | showClean | bool | true | Statusvariable für DB-Bereinigung anzeigen |
| Zeige Scan-Aktion | showDoScan | bool | true | Aktionsvariable zum Starten des Scans anlegen |
| Zeige Clean-Aktion | showDoClean | bool | true | Aktionsvariable zum Starten der Bereinigung anlegen |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| Scannen | bool | Scan | Status des Datenbank-Scans |
| Bereinigen | bool | Clean | Status der Datenbank-Bereinigung |

## 6. Visualisierung

### Kachel Visualisierung

Darstellung als Kachel:  
![VideoLibrary](imgs/VideoLib.png)

### WebFront Visualisierung

![VideoLibrary WebFront](imgs/VideoLib_WF.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.

```php
bool KODIVIDEOLIB_Scan(int $InstanzID)
```

Startet das Scannen der Video-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIVIDEOLIB_Clean(int $InstanzID)
```

Startet das Bereinigen der Video-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIVIDEOLIB_Export(int $InstanzID, string $Path, bool $Overwrite, bool $includeImages)
```

Exportiert die Video-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetMovieDetails(int $InstanzID, int $MovieId)
```

Liest die Eigenschaften eines Films aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetMovies(int $InstanzID)
```

Liest alle Filme aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetTVShowDetails(int $InstanzID, int $TvShowId)
```

Liest die Eigenschaften einer TV-Serie aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetTVShows(int $InstanzID)
```

Liest alle TV-Serien aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetEpisodeDetails(int $InstanzID, int $EpisodeId)
```

Liest die Eigenschaften einer Episode aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIVIDEOLIB_GetEpisodes(int $InstanzID)
```

Liest alle Episoden aus.  
Liefert ein Array bei Erfolg, sonst `false`.

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

- Scan und Clean über Buttons startbar
- Export über PHP-Befehl möglich

## 9. Anhang

### 1. Changelog

[Changelog der Library](../README.md#2-changelog)

### 2. Spenden

Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

PayPal:  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)

Wunschliste:  
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

IPS-Modul:  
[Custom NC-SA](../LICENSE)