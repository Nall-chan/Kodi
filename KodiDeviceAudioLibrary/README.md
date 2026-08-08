[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi Audio Datenbank (KodiDeviceAudioLibrary)  <!-- omit in toc -->

Ermöglicht das Auslesen, Scannen, Bereinigen und Exportieren der Audio-Datenbank von Kodi in IP-Symcon.

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

- Zugriff auf die Kodi-Audio-Datenbank.
- Auslesen von Künstlern, Alben, Songs und Genres.
- Ausführen von Scannen und Bereinigen der Datenbank.
- Exportieren der Datenbank.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Eingerichtete Musikbibliothek in Kodi

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
| Zeige Scan-Aktion | showDoScan | bool | true | Aktions-Variable zum Starten des Scans anlegen |
| Zeige Clean-Aktion | showDoClean | bool | true | Aktions-Variable zum Starten der Bereinigung anlegen |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| Scannen | bool | Scan | Status des Datenbank-Scans |
| Bereinigen | bool | Clean | Status der Datenbank-Bereinigung |

## 6. Visualisierung

### Kachel Visualisierung

Darstellung als Kachel:  
![AudioLibrary](imgs/AudioLib.png)

### WebFront Visualisierung

![AudioLibrary WebFront](imgs/AudioLib_WF.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.

```php
bool KODIAUDIOLIB_Scan(int $InstanzID)
```

Startet das Scannen der Audio-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIAUDIOLIB_Clean(int $InstanzID)
```

Startet das Bereinigen der Audio-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIAUDIOLIB_Export(int $InstanzID, string $Path, bool $Overwrite, bool $includeImages)
```

Exportiert die Audio-Datenbank.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetAlbumDetails(int $InstanzID, int $AlbumID)
```

Liest die Eigenschaften eines Albums aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

| Index | Typ | Beschreibung |
| :--- | :---: | :--- |
| theme | string[] | Themen |
| description | string | Beschreibung |
| type | string | Typ |
| style | string[] | Stilrichtungen |
| albumid | int | ID des Albums |
| playcount | int | Anzahl der Wiedergaben |
| albumlabel | string | Plattenlabel |
| mood | string[] | Stimmungen |
| displayartist | string | Angezeigter Künstler |
| artist | string[] | Künstler |
| genreid | int[] | Genre-IDs |
| musicbrainzalbumartistid | string | MusicBrainz AlbumArtistID |
| year | int | Erscheinungsjahr |
| rating | int | Bewertung |
| artistid | int[] | Künstler-IDs |
| title | string | Titel des Albums |
| musicbrainzalbumid | string | MusicBrainz AlbumID |
| genre | string[] | Genres |
| fanart | string | Pfad zum Fanart |
| thumbnail | string | Pfad zum Cover |

---

```php
array|bool KODIAUDIOLIB_GetAlbums(int $InstanzID)
```

Liest alle Alben aus der Datenbank aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetRecentlyAddedAlbums(int $InstanzID)
```

Liest die zuletzt hinzugefügten Alben aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetRecentlyPlayedAlbums(int $InstanzID)
```

Liest die zuletzt abgespielten Alben aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetArtistDetails(int $InstanzID, int $ArtistID)
```

Liest die Eigenschaften eines Künstlers aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

| Index | Typ | Beschreibung |
| :--- | :---: | :--- |
| born | string | Geburtsdatum |
| formed | string | Gründungsdatum |
| died | string | Todesdatum |
| style | string[] | Stilrichtungen |
| yearsactive | string[] | Aktive Jahre |
| mood | string[] | Stimmungen |
| musicbrainzartistid | string[] | MusicBrainz ArtistID |
| disbanded | string | Auflösungsdatum |
| description | string | Beschreibung |
| artist | string | Name des Künstlers |
| instrument | string[] | Instrumente |
| artistid | int | ID des Künstlers |
| genre | string[] | Genres |
| fanart | string | Pfad zum Fanart |
| thumbnail | string | Pfad zum Cover |

---

```php
array|bool KODIAUDIOLIB_GetArtists(int $InstanzID)
```

Liest alle Künstler aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetGenres(int $InstanzID)
```

Liest alle Genres aus.  
Liefert ein Array bei Erfolg, sonst `false`.

| Index | Typ | Beschreibung |
| :--- | :---: | :--- |
| genreid | int | ID des Genres |
| fanart | string | Pfad zum Fanart |
| thumbnail | string | Pfad zum Cover |

---

```php
array|bool KODIAUDIOLIB_GetSongDetails(int $InstanzID, int $SongID)
```

Liest die Eigenschaften eines Songs aus.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetSongs(int $InstanzID)
```

Liest alle Songs aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetRecentlyAddedSongs(int $InstanzID)
```

Liest die zuletzt hinzugefügten Songs aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIAUDIOLIB_GetRecentlyPlayedSongs(int $InstanzID)
```

Liest die zuletzt abgespielten Songs aus.  
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