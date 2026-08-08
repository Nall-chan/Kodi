[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Files <!-- omit in toc -->

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

- Anzeige von Medienquellen (Video, Musik, Bilder, Dateien, Programme)
- Navigation durch Verzeichnisstrukturen
- Abruf von Dateiinformationen

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Eingerichtete Quellen in Kodi

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceFiles)
3. Mit übergeordnetem KodiSplitter verbinden

**Konfigurationsseite:**

Keine spezifischen Eigenschaften vorhanden.

## 5. Statusvariablen

Keine automatischen Statusvariablen - wird über PHP-Befehle abgefragt

## 6. Visualisierung

Keine direkte Visualisierung - wird über eigene Skripte realisiert

## 7. PHP-Befehlsreferenz

 ```php
array|boolean KODIFILES_GetSources(integer $InstanzeID, string $Media);
```
 Liefert alle bekannten Quellen nach Typ $Value.
 $Media: Der Typ der zu suchenden Quellen.
    "video"=Video
    "music"=Musik
    "pictures"=Bilder
    "files"=Dateien
    "programs"=Programme
 Rückgabewert ist ein Array mit den Quellen oder FALSE bei einem Fehler.

| Index |  Typ   |      Beschreibung      |
| :---: | :----: | :--------------------: |
| file  | string | Verzeichnis der Quelle |
| label | string |    Name der Quelle     |

 ```php
array|boolean KODIFILES_GetFileDetails(integer $InstanzeID, string $File, string $Media);
```
 Liefert alle Details einer Datei.
 $File : Dateiname
 $Media: Der Typ der zu suchenden Quellen.
    "video"=Video
    "music"=Musik
    "pictures"=Bilder
    "files"=Dateien
    "programs"=Programme
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.

|          Index           |     Typ     |        Beschreibung        |
| :----------------------: | :---------: | :------------------------: |
|         filetyp          |   string    |                            |
|           size           |   integer   |                            |
|         mimetype         |   integer   |                            |
|           file           |   string    |                            |
|       lastmodified       |   string    |                            |
|        sorttitle         |   string    |                            |
|      productioncode      |   string    |                            |
|           cast           |    array    |                            |
|          votes           |   string    |                            |
|         duration         |   integer   |                            |
|         trailer          |   string    |                            |
|         albumid          |   integer   |                            |
|   musicbrainzartistid    |   string    |                            |
|           mpaa           |   string    |                            |
|        albumlabel        |   string    |                            |
|      originaltitle       |   string    |                            |
|          writer          |  string[]   |                            |
|      albumartistid       |  integer[]  |                            |
|           type           |   string    |                            |
|         episode          |   integer   |                            |
|        firstaired        |   string    |                            |
|        showtitle         |   string    |                            |
|         country          | string[]  ] |
|           mood           |  string[]   |                            |
|           set            |   string    |                            |
|    musicbrainztrackid    |   string    |                            |
|           tag            |  string[]   |                            |
|          lyrics          |   string    |                            |
|          top250          |   integer   |                            |
|         comment          |   string    |                            |
|        premiered         |   string    |                            |
|         showlink         |  string[]   |                            |
|          style           |  string[]   |                            |
|          album           |   string    |                            |
|         tvshowid         |   integer   |                            |
|          season          |   integer   |                            |
|          theme           |  string[]   |                            |
|       description        |   string    |                            |
|          setid           |   integer   |                            |
|          track           |   integer   |                            |
|         tagline          |   string    |                            |
|       plotoutline        |   string    |                            |
|     watchedepisodes      |   integer   |                            |
|            id            |   integer   |                            |
|           disc           |   integer   |                            |
|       albumartist        |  string[]   |                            |
|          studio          |  string[]   |                            |
|         uniqueid         |    array    |                            |
|       episodeguide       |   string    |                            |
|        imdbnumber        |   string    |                            |
|        dateadded         |   string    |                            |
|        lastplayed        |   string    |                            |
|           plot           |   string    |                            |
|      streamdetails       |    array    |                            |
|         director         |  string[]   |                            |
|          resume          |    array    |                            |
|         runtime          |   integer   |                            |
|           art            |    array    |                            |
|        playcount         |   integer   |   Anzahl der Wiedergaben   |
|      displayartist       |   string    |          Künstler          |
|          artist          |  string[]   |     Array der Künstler     |
|         genreid          |  integer[]  |    Array der Genre IDs     |
| musicbrainzalbumartistid |   string    | Music Brainz AlbumArtistID |
|           year           |   integer   |      Erscheinungsjahr      |
|          rating          |   integer   |         Bewertung          |
|         artistid         |  integer[]  |   Array der Künstler IDs   |
|          title           |   string    |      Titel der Datei       |
|    musicbrainzalbumid    |   string    |    Music Brainz AlbumID    |
|          genre           |  string[]   |      Array der Genres      |
|          fanart          |   string    |      Pfad zum Fanart       |
|        thumbnail         |   string    |       Pfad zum Cover       |

 ```php
array|boolean KODIFILES_GetDirectory(integer $InstanzeID, string $Directory);
```
 Liefert Informationen zu einem Verzeichnis.
 $Directory : Verzeichnis
 Rückgabewert ist ein Array mit den Eigenschaften des Verzeichnisses FALSE bei einem Fehler.
 Es gilt die Tabelle von KODIFILES_GetFileDetails.

 ```php
array|boolean KODIFILES_GetDirectoryDetails(integer $InstanzeID, string $Directory, string $Media);
```
 Liefert alle Details eines Verzeichnisses.
 $Directory : Verzeichnis
 $Media: Der Typ der zu suchenden Quellen.
    "video"=Video
    "music"=Musik
    "pictures"=Bilder
    "files"=Dateien
    "programs"=Programme
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.
 Es gilt die Tabelle von KODIFILES_GetFileDetails.

## 8. Aktionen

Keine direkten Aktionen - wird über PHP-Befehle gesteuert

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
