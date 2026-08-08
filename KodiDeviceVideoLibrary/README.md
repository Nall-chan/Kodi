[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi VideoLibrary <!-- omit in toc -->

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen](#5-statusvariablen)
- [6. Visualisierung](#6-visualisierung)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Aktionen](#8-aktionen)
- [9. Anhang](#9-anhang)
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

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceVideoLibrary)
3. Mit übergeordnetem KodiSplitter verbinden

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| showScan | boolean | true | Statusvariable für DB-Scanner verwenden |
| showDoScan | boolean | true | Statusvariable für DB-Bereinigung verwenden |
| showClean | boolean | true | Aktions-Variable zum starten des Scan anlegen |
| showDoClean | boolean | true | Aktions-Variable zum starten der Bereinigung anlegen |

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
```
 Startet das bereinigen der Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIVIDEOLIB_Clean(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
array|boolean KODIVIDEOLIB_GetEpisodeDetails(integer $InstanzeID, integer $EpisodeId);
```
 Liest die Eigenschaften einer Episode aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|     Index      |   Typ    |      Beschreibung      |
| :------------: | :------: | :--------------------: |
|      cast      |  array   |                        |
| productioncode |  string  |                        |
|     rating     | integer  |       Bewertung        |
|     votes      |  string  |                        |
|    episode     | integer  |                        |
|   showtitle    |  string  |                        |
|   episodeid    | integer  |                        |
|    tvshowid    | integer  |                        |
|     season     | integer  |                        |
|   firstaired   |  string  |                        |
|    uniqueid    |  array   |                        |
| originaltitle  |  string  |                        |
|     writer     | string[] |                        |
| streamdetails  |  array   |                        |
|    director    | string[] |                        |
|     resume     |  array   |                        |
|    runtime     | integer  |                        |
|   dateadded    |  string  |                        |
|      file      |  string  |                        |
|   lastplayed   |  string  |                        |
|      plot      |  string  |                        |
|     title      |  string  |    Titel der Datei     |
|      art       |  array   |                        |
|   playcount    | integer  | Anzahl der Wiedergaben |
|     fanart     |  string  |    Pfad zum Fanart     |
|   thumbnail    |  string  |     Pfad zum Cover     |

```php
array|boolean KODIVIDEOLIB_GetEpisodes(integer $InstanzeID);
```
 Liest die Eigenschaften aller Episoden aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetEpisodeDetails.

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Episoden aus, welche zuletzt zur Datenbank hinzugefügt wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetEpisodeDetails.

```php
array|boolean KODIVIDEOLIB_GetGenres(integer $InstanzeID);
```
 Liest die Eigenschaften aller bekannten Genres aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|   Index   |   Typ   |  Beschreibung   |
| :-------: | :-----: | :-------------: |
|  genreid  | integer |  ID des Genres  |
|  fanart   | string  | Pfad zum Fanart |
| thumbnail | string  | Pfad zum Cover  |

```php
array|boolean KODIVIDEOLIB_GetMovieDetails(integer $InstanzeID, integer $MovieId);
```
 Liest die Eigenschaften eines Film aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|     Index     |     Typ     |      Beschreibung      |
| :-----------: | :---------: | :--------------------: |
|  plotoutline  |   string    |                        |
|   sorttitle   |   string    |                        |
|    movieid    |   integer   |                        |
|     cast      |    array    |                        |
|     votes     |   string    |                        |
|   showlink    |  string[]   |                        |
|    top250     |   integer   |                        |
|    trailer    |   string    |                        |
|     year      |   integer   |    Erscheinungsjahr    |
|    rating     |   integer   |       Bewertung        |
|     year      |   integer   |    Erscheinungsjahr    |
|    country    | string[]  ] |
|    studio     |  string[]   |                        |
|      set      |   string    |                        |
|     genre     |  string[]   |    Array der Genres    |
|     mpaa      |   string    |                        |
|     setid     |   integer   |                        |
|    rating     |   integer   |       Bewertung        |
|      tag      |  string[]   |                        |
|    tagline    |   string    |                        |
|    writer     |  string[]   |                        |
| originaltitle |   string    |                        |
|  imdbnumber   |   string    |                        |
| streamdetails |    array    |                        |
|   director    |  string[]   |                        |
|    resume     |    array    |                        |
|    runtime    |   integer   |                        |
|   dateadded   |   string    |                        |
|     file      |   string    |                        |
|  lastplayed   |   string    |                        |
|     plot      |   string    |                        |
|     title     |   string    |    Titel der Datei     |
|      art      |    array    |                        |
|   playcount   |   integer   | Anzahl der Wiedergaben |
|    fanart     |   string    |    Pfad zum Fanart     |
|   thumbnail   |   string    |     Pfad zum Cover     |

```php
array|boolean KODIVIDEOLIB_GetMovies(integer $InstanzeID);
```
 Liest die Eigenschaften aller Filme aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieDetails.

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedMovies(integer $InstanzeID);
```
 Liest die Eigenschaften der Filme aus, welche zuletzt zur Datenbank hinzugefügt wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieDetails.

```php
array|boolean KODIVIDEOLIB_GetMovieSetDetails(integer $InstanzeID, integer $SetId);
```
 Liest die Eigenschaften eines Film-Sets aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|   Index   |   Typ   |           Beschreibung            |
| :-------: | :-----: | :-------------------------------: |
|  movies   |  array  | Ein Array mit allen Film-Objekten |
|   setid   | integer |                                   |
|   title   | string  |               Titel               |
|    art    |  array  |                                   |
| playcount | integer |      Anzahl der Wiedergaben       |
|  fanart   | string  |          Pfad zum Fanart          |
| thumbnail | string  |          Pfad zum Cover           |

```php
array|boolean KODIVIDEOLIB_GetMovieSets(integer $InstanzeID);
```
 Liest die Eigenschaften aller Film-Sets aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetMovieSetDetails.

```php
array|boolean KODIVIDEOLIB_GetMusicVideoDetails(integer $InstanzeID, integer $MusicVideoId);
```
 Liest die Eigenschaften eines Musikvideos aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|     Index     |   Typ    |      Beschreibung      |
| :-----------: | :------: | :--------------------: |
|     genre     | string[] |    Array der Genres    |
|    artist     | string[] |   Array der Künstler   |
| musicvideoid  | integer  |                        |
|      tag      | string[] |                        |
|     album     |  string  |                        |
|     track     | integer  |                        |
|    studio     | string[] |                        |
|     year      | integer  |    Erscheinungsjahr    |
| streamdetails |  array   |                        |
|   director    | string[] |                        |
|    resume     |  array   |                        |
|    runtime    | integer  |                        |
|   dateadded   |  string  |                        |
|     file      |  string  |                        |
|  lastplayed   |  string  |                        |
|     plot      |  string  |                        |
|     title     |  string  |    Titel der Datei     |
|      art      |  array   |                        |
|   playcount   | integer  | Anzahl der Wiedergaben |
|    fanart     |  string  |    Pfad zum Fanart     |
|   thumbnail   |  string  |     Pfad zum Cover     |

```php
array|boolean KODIVIDEOLIB_GetMusicVideos(integer $InstanzeID);
```
 Liest die Eigenschaften aller Musikvideos aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetMusicVideoDetails.

```php
array|boolean KODIVIDEOLIB_GetRecentlyAddedMusicVideos(integer $InstanzeID);
```
 Liest die Eigenschaften der Musikvideos aus, welche zuletzt zur Datenbank hinzugefügt wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetMusicVideoDetails.

```php
array|boolean KODIVIDEOLIB_GetSeasons(integer $InstanzeID, integer $TvShowId);
```
 Liest die Eigenschaften Alles Seasons eine TV-Serie ($TvShowId) aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|      Index      |   Typ   |      Beschreibung      |
| :-------------: | :-----: | :--------------------: |
|    showtitle    | string  |                        |
| watchedepisodes | integer |                        |
|    tvshowid     | integer |                        |
|     episode     | integer |                        |
|     season      | integer |                        |
|       art       |  array  |                        |
|    playcount    | integer | Anzahl der Wiedergaben |
|     fanart      | string  |    Pfad zum Fanart     |
|    thumbnail    | string  |     Pfad zum Cover     |

```php
array|boolean KODIVIDEOLIB_GetTVShowDetails(integer $InstanzeID, integer $TvShowId);
```
 Liest die Eigenschaften eines TV-Serie aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|      Index      |   Typ    |      Beschreibung      |
| :-------------: | :------: | :--------------------: |
|    sorttitle    |  string  |                        |
|      mpaa       |  string  |                        |
|    premiered    |  string  |                        |
|      year       | integer  |    Erscheinungsjahr    |
|     episode     | integer  |                        |
| watchedepisodes | integer  |                        |
|      votes      |  string  |                        |
|     rating      | integer  |       Bewertung        |
|    tvshowid     | integer  |                        |
|     studio      | string[] |                        |
|     season      | integer  |                        |
|      genre      | string[] |    Array der Genres    |
|      cast       |  array   |                        |
|  episodeguide   |  string  |                        |
|       tag       | string[] |                        |
|  originaltitle  |  string  |                        |
|   imdbnumber    |  string  |                        |
|    dateadded    |  string  |                        |
|      file       |  string  |                        |
|   lastplayed    |  string  |                        |
|      plot       |  string  |                        |
|      title      |  string  |    Titel der Datei     |
|       art       |  array   |                        |
|    playcount    | integer  | Anzahl der Wiedergaben |
|     fanart      |  string  |    Pfad zum Fanart     |
|    thumbnail    |  string  |     Pfad zum Cover     |

```php
array|boolean KODIVIDEOLIB_GetTVShows(integer $InstanzeID);
```
 Liest die Eigenschaften aller TV-Serien.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIVIDEOLIB_GetTVShowDetails.

```php
boolean KODIVIDEOLIB_Export(integer $InstanzeID, string $Path, boolean $Overwrite, boolean $includeImages);
```
 Exportiert die Audio Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Scan und Clean über Buttons startbar
- Export über PHP-Befehl möglich

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
