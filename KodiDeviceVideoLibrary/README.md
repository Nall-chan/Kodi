[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi VideoLibrary <!-- omit in toc -->  

## Inhaltsverzeichnis

- [Inhaltsverzeichnis](#inhaltsverzeichnis)
- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen](#5-statusvariablen)
- [6. Visualisierung](#6-visualisierung)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Aktionen](#8-aktionen)
- [9. Anhang](#9-anhang)
  - [Eigenschaften](#eigenschaften)
  - [Changelog](#changelog)
  - [Spenden](#spenden)
- [10. Lizenz](#10-lizenz)

## 1. Funktionsumfang

- Zugriff auf die Kodi-Video-Datenbank
- Anzeige von Filmen, Serien und Musikvideos
- Scannen und Bereinigen der Datenbank
- Export der Datenbank

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Eingerichtete Videobibliothek in Kodi

## 3. Software-Installation

Die Installation erfolgt über den Module Store von IP-Symcon oder automatisch über den KodiConfigurator.

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceVideoLibrary)
3. Mit übergeordnetem KodiSplitter verbinden

## 5. Statusvariablen

- Scan-Status
- Clean-Status
- Zuletzt hinzugefügte Filme/Serien
- Zuletzt abgespielte Filme/Serien

## 6. Visualisierung

![VideoLibrary](imgs/VideoLib.png)
![VideoLibrary WebFront](imgs/VideoLib_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODIVIDEOLIB_Scan(integer $InstanzeID);
boolean KODIVIDEOLIB_Clean(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetEpisodeDetails(integer $InstanzeID, integer $EpisodeId);
array|boolean KODIVIDEOLIB_GetEpisodes(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetGenres(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetMovieDetails(integer $InstanzeID, integer $MovieId);
array|boolean KODIVIDEOLIB_GetMovies(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetMovieSetDetails(integer $InstanzeID, integer $SetId);
array|boolean KODIVIDEOLIB_GetMovieSets(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetMusicVideoDetails(integer $InstanzeID, integer $MusicVideoId);
array|boolean KODIVIDEOLIB_GetMusicVideos(integer $InstanzeID);
array|boolean KODIVIDEOLIB_GetSeasons(integer $InstanzeID, integer $TvShowId);
array|boolean KODIVIDEOLIB_GetTVShowDetails(integer $InstanzeID, integer $TvShowId);
array|boolean KODIVIDEOLIB_GetTVShows(integer $InstanzeID);
boolean KODIVIDEOLIB_Export(integer $InstanzeID, string $Path, boolean $Overwrite, boolean $includeImages);
```

## 8. Aktionen

- Scan und Clean über Buttons startbar
- Export über PHP-Befehl möglich

## 9. Anhang

### Eigenschaften

| Eigenschaft |   Typ   | Standardwert |                       Funktion                       |
| :---------: | :-----: | :----------: | :--------------------------------------------------: |
|  showScan   | boolean |     true     |       Statusvariable für DB-Scanner verwenden        |
| showDoScan  | boolean |     true     |     Statusvariable für DB-Bereinigung verwenden      |
|  showClean  | boolean |     true     |    Aktions-Variable zum starten des Scan anlegen     |
| showDoClean | boolean |     true     | Aktions-Variable zum starten der Bereinigung anlegen |

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
