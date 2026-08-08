[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Player <!-- omit in toc -->

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

- Steuerung der Wiedergabe (Play, Pause, Stop, Geschwindigkeit)
- Anzeige des aktuellen Wiedergabestatus
- Navigation in Playlists
- Anzeige des aktuellen Medieninhalts (Cover, Titel, Fortschritt)
- Steuerung von Untertiteln und Audiospuren

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Player-Typ konfiguriert (Audio, Video oder Bilder)

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDevicePlayer)
3. Player-Typ auswählen (0=Audio, 1=Video, 2=Bilder)
4. Mit übergeordnetem KodiSplitter verbinden

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| PlayerID | integer | 0 | Playermodus: 0 = Audio, 1 = Video, 2 = Bilder |
| CoverSize | integer | 300 | Die Höhe vom Cover in Pixel auf welche das 'Cover' skaliert wird |
| CoverTyp | string | thumb | Varianten: 'thumb', 'album', 'artist', 'fanart', 'poster', 'banner' |

## 5. Statusvariablen

- Wiedergabestatus (speed, time, percentage)
- Partymodus
- Wiederholung
- Zufallswiedergabe
- Aktueller Titel

## 6. Visualisierung

![Audio Player](imgs/AudioPlayer.png)
![Picture Player](imgs/PicturePlayer.png)
![Video Player](imgs/VideoPlayer.png)
![Audio Player WebFront](imgs/AudioPlayer_WF.png)
![Picture Player WebFront](imgs/PicturePlayer_WF.png)
![Video Player WebFront](imgs/VideoPlayer_WF.png)

## 7. PHP-Befehlsreferenz

 ```php
array | boolean KODIPLAYER_GetItem(integer $InstanzeID);
```  
 Holt die Daten des aktuellen wiedergegebenen Items, und gibt Diese als Array zurück.
 Rückgabewert ist ein Array mit den Eigenschaften oder FALSE bei einem Fehler.

|          Index           |     Typ     |        Beschreibung        |
| :----------------------: | :---------: | :------------------------: |
|         channel          |   string    |                            |
|       channeltype        |   string    |                            |
|           file           |   string    |                            |
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
|          hidden          |   boolean   |                            |
|          locked          |   boolean   |                            |
|      channelnumber       |   integer   |                            |
|         endtime          |   string    |                            |
|        starttime         |   string    |                            |

 ```php
boolean KODIPLAYER_GoToNext(integer $InstanzeID);
```  
 Springt zum nächsten Item in der Wiedergabeliste.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_GoToPrevious(integer $InstanzeID);
```  
 Springt zum vorherigen Item in der Wiedergabeliste.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_GoToTrack(integer $InstanzeID, integer $Value);
```  
 Springt auf ein bestimmtes Item in der Wiedergabeliste.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadAlbum(integer $InstanzeID, integer $AlbumId);
```  
 Lädt ein Album und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadArtist(integer $InstanzeID, integer $ArtistId);
```  
 Lädt alle Item eines Artist und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadChannel(integer $InstanzeID, integer $ChannelId);
```  
 Lädt einen PVR-Kanal für die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadDirectory(integer $InstanzeID, string $Directory);
```  
 Lädt alle Item eines Verzeichnisses und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadDirectoryRecursive(integer $InstanzeID, string $Directory);
```  
 Lädt alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadEpisode(integer $InstanzeID, integer $EpisodeId);
```  
 Lädt eine Episode und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadFile(integer $InstanzeID, string $File);
```  
 Lädt eine Datei und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadGenre(integer $InstanzeID, integer $GenreId);
```  
 Lädt eine komplettes Genre und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadMovie(integer $InstanzeID, integer $MovieId);
```  
 Lädt ein Film und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadMusicVideo(integer $InstanzeID, integer $MusicvideoId);
```  
 Lädt ein Musicvideo und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadPlaylist(integer $InstanzeID);
```  
 Lädt die Playlist des Players und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadRecording(integer $RecordingId);
```  
 Lädt eine PVR-Aufnahme startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_LoadSong(integer $InstanzeID, integer $SongId);
```  
 Lädt ein Songs und startet die Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_Pause(integer $InstanzeID);
```  
 Pausiert die Wiedergabe des aktuellen Items.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_Play(integer $InstanzeID);
```  
 Starte die Wiedergabe des aktuelle pausierten Items.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetAudioStream(integer $InstanzeID, integer $Value);
```  
 Aktiviert den Audiostream welcher als Index in $Value übergeben wurde.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetPartymode(integer $InstanzeID, boolean $Value);
```  
 Aktiviert (TRUE) oder deaktiviert (FALSE) den Partymodus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetPosition(integer $InstanzeID, integer $Value);
```  
 Springt auf eine absolute Position innerhalb der aktuellen Wiedergabe.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetRepeat(integer $InstanzeID, integer $Value);
```  
 Setzten den Wiederholungsmodus.
 $Value darf folgende Werte enthalten:
  [0=aus, 1=Titel, 2=Alle]
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetShuffle(integer $InstanzeID, boolean $Value);
```  
 Aktiviert (TRUE) oder deaktiviert (FALSE) den Zufallsmodus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetSpeed(integer $InstanzeID, integer $Value);
```  
 Setzten die Abspielgeschwindigkeit.
 $Value darf folgende Werte enthalten:
  [-32, -16, -8, -4, -2, 0, 1, 2, 4, 8, 16, 32]
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_SetSubtitle(integer $InstanzeID, integer $Value);
```  
 Aktiviert einen  Untertitel welcher als Index in $Value übergeben wurde.
 Wird -1 übergeben, wird der Untertitel deaktiviert.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYER_Stop(integer $InstanzeID);
```  
 Stoppt die Wiedergabe des aktuellen Items.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Wiedergabe steuern über Buttons
- Position in der Wiedergabe anpassen
- Audiospur und Untertitel auswählen

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
