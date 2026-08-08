[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Playlist <!-- omit in toc -->

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

- Anzeige und Verwaltung der Kodi-Playlists
- Hinzufügen/Entfernen von Einträgen
- Navigation in der Playlist
- Visualisierung der Playlist-Inhalte

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Player-Typ konfiguriert (Audio, Video oder Bilder)

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDevicePlaylist)
3. Playlist-Typ auswählen (0=Audio, 1=Video, 2=Bilder)
4. Mit übergeordnetem KodiSplitter verbinden

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| PlaylistID | integer | 0 | Playermodus: 0 = Audio, 1 = Video, 2 = Bilder |
| showPlaylist | boolean | true | HTML-Tabelle mit der Playlist erzeugen |
| Playlistconfig | integer | auto | Script mit den Style für die HTML-Tabelle |

## 5. Statusvariablen

- Playlist-Inhalt (HTML-Tabelle)

## 6. Visualisierung

![Audio Playlist](imgs/AudioPlaylist.png)
![Picture Playlist](imgs/PicturePlaylist.png)
![Video Playlist](imgs/VideoPlaylist.png)
![Audio Playlist WebFront](imgs/AudioPlaylist_WF.png)
![Picture Playlist WebFront](imgs/PicturePlaylist_WF.png)
![Video Playlist WebFront](imgs/VideoPlaylist_WF.png)

## 7. PHP-Befehlsreferenz

Hinweis: Ein mischen von verschiedenen Medien (Audio, Video, Bilder) ist nicht möglich.

 ```php
boolean KODIPLAYLIST_AddAlbum(integer $InstanzeID, integer $AlbumId);
```  
 Fügt der Playlist ein Album hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddArtist(integer $InstanzeID, integer $ArtistId);
```  
 Fügt der Playlist alle Item eines Artist hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddDirectory(integer $InstanzeID, string $Directory);
```  
 Fügt der Playlist alle Item eines Verzeichnisses hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddDirectoryRecursive(integer $InstanzeID, string $Directory);
```  
 Fügt der Playlist alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddEpisode(integer $InstanzeID, integer $EpisodeId);
```  
 Fügt der Playlist eine Episode hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddFile(integer $InstanzeID, string $File);
```  
 Fügt der Playlist eine Datei hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddGenre(integer $InstanzeID, integer $GenreId);
```  
 Fügt der Playlist eine komplettes Genre hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddMovie(integer $InstanzeID, integer $MovieId);
```  
 Fügt der Playlist ein Film hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddMusicVideo(integer $InstanzeID, integer $MusicvideoId);
```  
 Fügt der Playlist ein Musicvideo hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_AddSong(integer $InstanzeID, integer $SongId);
```  
 Fügt der Playlist ein Song hinzu.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_Clear(integer $InstanzeID);
```  
 Leert die Playlist.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_Get(integer $InstanzeID);
```  
 Gibt alle Einträge Einträge der Playlist als Array zurück.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE k.

|    Index    |   Typ    |    Beschreibung    |
| :---------: | :------: | :----------------: |
|    title    |  string  |  Titel der Datei   |
|   artist    | string[] | Array der Künstler |
| albumartist | string[] |                    |
|    genre    | string[] |  Array der Genres  |
|    year     | integer  |  Erscheinungsjahr  |
|    album    |  string  |                    |
|    track    | integer  |                    |
|  duration   | integer  |                    |
|    plot     |  string  |                    |
|   runtime   | integer  |                    |
|   season    | integer  |                    |
|   episode   | integer  |                    |
|  showtitle  |  string  |                    |
|  thumbnail  |  string  |   Pfad zum Cover   |
|    file     |  string  |                    |
|    disc     | integer  |                    |
| albumlabel  |  string  |                    |

 ```php
boolean KODIPLAYLIST_InsertAlbum(integer $InstanzeID, integer $AlbumId, integer $Position);
```  
 Fügt in der Playlist ein Album ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertArtist(integer $InstanzeID, integer $ArtistId, integer $Position);
```  
 Fügt in der Playlist alle Item eines Artist ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertDirectory(integer $InstanzeID, string $Directory, integer $Position);
```  
 Fügt in der Playlist alle Item eines Verzeichnisses ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertDirectoryRecursive(integer $InstanzeID, string $Directory, integer $Position);
```  
 Fügt in der Playlist alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertEpisode(integer $InstanzeID, integer $EpisodeId, integer $Position);
```  
 Fügt in der Playlist eine Episode ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertFile(integer $InstanzeID, string $File, integer $Position);
```  
 Fügt in der Playlist eine Datei ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertGenre(integer $InstanzeID, integer $GenreId, integer $Position);
```  
 Fügt in der Playlist eine komplettes Genre ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertMovie(integer $InstanzeID, integer $MovieId, integer $Position);
```  
 Fügt in der Playlist ein Film ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertMusicVideo(integer $InstanzeID, integer $MusicvideoId, integer $Position);
```  
 Fügt in der Playlist ein Musicvideo ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_InsertSong(integer $InstanzeID, integer $SongId, integer $Position);
```  
 Fügt in der Playlist ein Song ein.
 Alle anderen Einträge werden automatisch nach hinten verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_Remove(integer $InstanzeID, integer $Position);
```  
 Entfernt einen Eintrag aus der Playlist.
 Alle anderen Einträge werden automatisch nach vorne verschoben.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODIPLAYLIST_Swap(integer $InstanzeID, integer $Position1, integer $Position2);
```  
 Tauscht zwei Einträge innerhalb der Playlist.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Playlist-Inhalte über Buttons verwalten
- Direktes Springen zu Einträgen
- Sortieren der Playlist

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
