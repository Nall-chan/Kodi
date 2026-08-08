[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi AudioLibrary <!-- omit in toc -->

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

- Zugriff auf die Kodi-Audio-Datenbank
- Anzeige von Künstlern, Alben und Songs
- Scannen und Bereinigen der Datenbank
- Export der Datenbank

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Eingerichtete Musikbibliothek in Kodi

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceAudioLibrary)
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
- Zuletzt hinzugefügte Alben/Songs
- Zuletzt abgespielte Alben/Songs

## 6. Visualisierung

![AudioLibrary](imgs/AudioLib.png)
![AudioLibrary WebFront](imgs/AudioLib_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODIAUDIOLIB_Scan(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIAUDIOLIB_Clean(integer $InstanzeID);
```
 Startet das bereinigen der Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIAUDIOLIB_Export(integer $InstanzeID, string $Path, boolean $Overwrite, boolean $includeImages);
```
 Exportiert die Audio Datenbank.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
array|boolean KODIAUDIOLIB_GetAlbumDetails(integer $InstanzeID, integer $AlbumID);
```
 Liest die Eigenschaften eines Album aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurückgegeben.

|          Index           |    Typ    |        Beschreibung        |
| :----------------------: | :-------: | :------------------------: |
|          theme           | string[]  |                            |
|       description        |  string   |        Beschreibung        |
|           type           |  string   |                            |
|          style           | string[]  |      Array der Stiele      |
|         albumid          |  integer  |                            |
|        playcount         |  integer  |   Anzahl der Wiedergaben   |
|        albumlabel        |  string   |                            |
|           mood           | string[]  |    Array der Stimmungen    |
|      displayartist       |  string   |          Künstler          |
|          artist          | string[]  |     Array der Künstler     |
|         genreid          | integer[] |    Array der Genre IDs     |
| musicbrainzalbumartistid |  string   | Music Brainz AlbumArtistID |
|           year           |  integer  |      Erscheinungsjahr      |
|          rating          |  integer  |         Bewertung          |
|         artistid         | integer[] |   Array der Künstler IDs   |
|          title           |  string   |      Titel des Album       |
|    musicbrainzalbumid    |  string   |    Music Brainz AlbumID    |
|          genre           | string[]  |      Array der Genres      |
|          fanart          |  string   |      Pfad zum Fanart       |
|        thumbnail         |  string   |       Pfad zum Cover       |

```php
array|boolean KODIAUDIOLIB_GetAlbums(integer $InstanzeID);
```
 Liest einen Teil der Eigenschaften aller Alben aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurückgegeben.
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.

```php
array|boolean KODIAUDIOLIB_GetRecentlyAddedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Alben aus, welche zuletzt zur Datenbank hinzugefügt wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurückgegeben.
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.

```php
array|boolean KODIAUDIOLIB_GetRecentlyPlayedAlbums(integer $InstanzeID);
```
 Liest die Eigenschaften der Alben aus, welche zuletzt zur wiedergegeben wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIAUDIOLIB_GetAlbumDetails.

```php
array|boolean KODIAUDIOLIB_GetArtistDetails(integer $InstanzeID, integer $ArtistID);
```
 Liest die Eigenschaften eines Künstlers aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|        Index        |   Typ    |   Beschreibung   |
| :-----------------: | :------: | :--------------: |
|        born         |  string  |                  |
|       formed        |  string  |                  |
|        died         |  string  |                  |
|        style        | string[] |                  |
|     yearsactive     | string[] |                  |
|        mood         | string[] |                  |
| musicbrainzartistid | string[] |                  |
|      disbanded      |  string  |                  |
|     description     |  string  |                  |
|       artist        |  string  |                  |
|     instrument      | string[] |                  |
|      artistid       | integer  |                  |
|        genre        | string[] | Array der Genres |
|       fanart        |  string  | Pfad zum Fanart  |
|      thumbnail      |  string  |  Pfad zum Cover  |

```php
array|boolean KODIAUDIOLIB_GetArtists(integer $InstanzeID);
```
 Liest die Eigenschaften aller Künstler aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIAUDIOLIB_GetArtistDetails.

```php
array|boolean KODIAUDIOLIB_GetGenres(integer $InstanzeID);
```
 Liest die Eigenschaften aller bekannten Genres aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|   Index   |   Typ   |  Beschreibung   |
| :-------: | :-----: | :-------------: |
|  genreid  | integer |  ID des Genres  |
|  fanart   | string  | Pfad zum Fanart |
| thumbnail | string  | Pfad zum Cover  |

```php
array|boolean KODIAUDIOLIB_GetSongDetails(integer $InstanzeID, integer $SongID);
```
 Liest die Eigenschaften eines Songs aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|          Index           |    Typ    |        Beschreibung        |
| :----------------------: | :-------: | :------------------------: |
|          lyrics          |  string   |                            |
|          songid          |  integer  |                            |
|      albumartistid       | integer[] |                            |
|           disc           |  integer  |                            |
|         comment          |  string   |                            |
|        playcount         |  integer  |   Anzahl der Wiedergaben   |
|          album           |  string   |                            |
|           file           |  string   |                            |
|        lastplayed        |  string   |                            |
|         albumid          |  integer  |                            |
|   musicbrainzartistid    |  string   |                            |
|       albumartist        | string[]  |                            |
|         duration         |  integer  |                            |
|    musicbrainztrackid    |  string   |                            |
|          track           |  integer  |                            |
|      displayartist       |  string   |          Künstler          |
|          artist          | string[]  |                            |
|         genreid          | integer[] |    Array der Genre IDs     |
| musicbrainzalbumartistid |  string   | Music Brainz AlbumArtistID |
|           year           |  integer  |      Erscheinungsjahr      |
|          rating          |  integer  |         Bewertung          |
|         artistid         | integer[] |   Array der Künstler IDs   |
|          title           |  string   |      Titel des Album       |
|    musicbrainzalbumid    |  string   |    Music Brainz AlbumID    |
|          genre           | string[]  |      Array der Genres      |
|          fanart          |  string   |      Pfad zum Fanart       |
|        thumbnail         |  string   |       Pfad zum Cover       |

```php
array|boolean KODIAUDIOLIB_GetSongs(integer $InstanzeID);
```
 Liest die Eigenschaften aller Songs aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.

```php
array|boolean KODIAUDIOLIB_GetRecentlyAddedSongs(integer $InstanzeID);
```
 Liest die Eigenschaften der Songs aus, welche zuletzt zur Datenbank hinzugefügt wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.

```php
array|boolean KODIAUDIOLIB_GetRecentlyPlayedSongs(integer $InstanzeID);
```
 Liest die Eigenschaften der Songs aus, welche zuletzt zur wiedergegeben wurden.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.
 Es gilt die Tabelle von KODIAUDIOLIB_GetSongDetails.

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
