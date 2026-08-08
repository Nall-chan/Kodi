[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi Playlist (KodiDevicePlaylist)  <!-- omit in toc -->

Ermöglicht das Verwalten, Bearbeiten und Darstellen von Kodi-Wiedergabelisten in IP-Symcon.

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

- Anzeige und Verwaltung der Kodi-Wiedergabelisten.
- Hinzufügen, Einfügen, Tauschen und Entfernen von Einträgen.
- Leeren und Abfragen von Playlists.
- Visualisierung der Playlist-Inhalte im WebFront und in der Kachel-Visualisierung.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Konfigurierter Playlist-Typ (Audio, Video oder Bilder)

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

Eine einfache Einrichtung ist über den Konfigurator [Kodi Konfigurator](../KodiConfigurator/README.md) möglich.  
Bei der manuellen Einrichtung ist die Instanz im Dialog `Instanz hinzufügen` unter dem Hersteller `Kodi` zu finden.

**Konfigurationsseite:**

| Name | Eigenschaft | Typ | Standardwert | Funktion |
| :--- | :--- | :---: | :--- | :--- |
| Playlist Typ | PlaylistID | int | 0 | 0 = Audio, 1 = Video, 2 = Bilder |
| Zeige Playlist | showPlaylist | bool | true | HTML-Tabelle mit der Playlist erzeugen |
| Style Playlist | Playlistconfig | int | 0 | Skript mit dem Style für die HTML-Tabelle |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| Playlist | string | Playlist | HTML-Box mit dem Inhalt der aktuellen Playlist |

## 6. Visualisierung

### Kachel Visualisierung

Darstellung als Kachel:  
![Audio Playlist](imgs/AudioPlaylist.png)  
![Picture Playlist](imgs/PicturePlaylist.png)  
![Video Playlist](imgs/VideoPlaylist.png)

### WebFront Visualisierung

![Audio Playlist WebFront](imgs/AudioPlaylist_WF.png)  
![Picture Playlist WebFront](imgs/PicturePlaylist_WF.png)  
![Video Playlist WebFront](imgs/VideoPlaylist_WF.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.  
*Hinweis:* Ein Mischen verschiedener Medienarten (Audio, Video, Bilder) in einer Playlist ist nicht möglich.

```php
bool KODIPLAYLIST_AddSong(int $InstanzID, int $SongId)
bool KODIPLAYLIST_AddAlbum(int $InstanzID, int $AlbumId)
bool KODIPLAYLIST_AddArtist(int $InstanzID, int $ArtistId)
bool KODIPLAYLIST_AddMovie(int $InstanzID, int $MovieId)
bool KODIPLAYLIST_AddEpisode(int $InstanzID, int $EpisodeId)
bool KODIPLAYLIST_AddMusicVideo(int $InstanzID, int $MusicvideoId)
bool KODIPLAYLIST_AddFile(int $InstanzID, string $File)
bool KODIPLAYLIST_AddDirectory(int $InstanzID, string $Directory)
bool KODIPLAYLIST_AddDirectoryRecursive(int $InstanzID, string $Directory)
```

Fügt Einträge ans Ende der Playlist an.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIPLAYLIST_InsertSong(int $InstanzID, int $SongId, int $Position)
bool KODIPLAYLIST_InsertAlbum(int $InstanzID, int $AlbumId, int $Position)
bool KODIPLAYLIST_InsertFile(int $InstanzID, string $File, int $Position)
```

Fügt Einträge an einer bestimmten Position in die Playlist ein.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIPLAYLIST_Remove(int $InstanzID, int $Position)
```

Entfernt einen Eintrag an der angegebenen Position aus der Playlist.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIPLAYLIST_Swap(int $InstanzID, int $Position1, int $Position2)
```

Tauscht zwei Einträge innerhalb der Playlist.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIPLAYLIST_Clear(int $InstanzID)
```

Leert die gesamte Playlist.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
array|bool KODIPLAYLIST_Get(int $InstanzID)
```

Gibt alle Einträge der Playlist als Array zurück.  
Liefert ein assoziatives Array bei Erfolg, sonst `false`.

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

- Playlist-Inhalte über Buttons verwalten
- Direktes Springen zu Einträgen
- Sortieren und Verändern der Playlist

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