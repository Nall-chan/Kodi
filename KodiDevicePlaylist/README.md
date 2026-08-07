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
  - [Eigenschaften](#eigenschaften)
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

Die Installation erfolgt über den Module Store von IP-Symcon oder automatisch über den KodiConfigurator.

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDevicePlaylist)
3. Playlist-Typ auswählen (0=Audio, 1=Video, 2=Bilder)
4. Mit übergeordnetem KodiSplitter verbinden

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

```php
boolean KODIPLAYLIST_AddAlbum(integer $InstanzeID, integer $AlbumId);
boolean KODIPLAYLIST_AddArtist(integer $InstanzeID, integer $ArtistId);
boolean KODIPLAYLIST_AddDirectory(integer $InstanzeID, string $Directory);
boolean KODIPLAYLIST_AddDirectoryRecursive(integer $InstanzeID, string $Directory);
boolean KODIPLAYLIST_AddEpisode(integer $InstanzeID, integer $EpisodeId);
boolean KODIPLAYLIST_AddFile(integer $InstanzeID, string $File);
boolean KODIPLAYLIST_AddGenre(integer $InstanzeID, integer $GenreId);
boolean KODIPLAYLIST_AddMovie(integer $InstanzeID, integer $MovieId);
boolean KODIPLAYLIST_AddMusicVideo(integer $InstanzeID, integer $MusicvideoId);
boolean KODIPLAYLIST_AddSong(integer $InstanzeID, integer $SongId);
boolean KODIPLAYLIST_Clear(integer $InstanzeID);
array|boolean KODIPLAYLIST_Get(integer $InstanzeID);
boolean KODIPLAYLIST_InsertAlbum(integer $InstanzeID, integer $AlbumId, integer $Position);
boolean KODIPLAYLIST_InsertArtist(integer $InstanzeID, integer $ArtistId, integer $Position);
boolean KODIPLAYLIST_InsertDirectory(integer $InstanzeID, string $Directory, integer $Position);
boolean KODIPLAYLIST_InsertDirectoryRecursive(integer $InstanzeID, string $Directory, integer $Position);
boolean KODIPLAYLIST_InsertEpisode(integer $InstanzeID, integer $EpisodeId, integer $Position);
boolean KODIPLAYLIST_InsertFile(integer $InstanzeID, string $File, integer $Position);
boolean KODIPLAYLIST_InsertGenre(integer $InstanzeID, integer $GenreId, integer $Position);
boolean KODIPLAYLIST_InsertMovie(integer $InstanzeID, integer $MovieId, integer $Position);
boolean KODIPLAYLIST_InsertMusicVideo(integer $InstanzeID, integer $MusicvideoId, integer $Position);
boolean KODIPLAYLIST_InsertSong(integer $InstanzeID, integer $SongId, integer $Position);
boolean KODIPLAYLIST_Remove(integer $InstanzeID, integer $Position);
boolean KODIPLAYLIST_Swap(integer $InstanzeID, integer $Position1, integer $Position2);
```

## 8. Aktionen

- Playlist-Inhalte über Buttons verwalten
- Direktes Springen zu Einträgen
- Sortieren der Playlist

## 9. Anhang

### Eigenschaften

|  Eigenschaft   |   Typ   | Standardwert |                   Funktion                    |
| :------------: | :-----: | :----------: | :-------------------------------------------: |
|   PlaylistID   | integer |      0       | Playermodus: 0 = Audio, 1 = Video, 2 = Bilder |
|  showPlaylist  | boolean |     true     |    HTML-Tabelle mit der Playlist erzeugen     |
| Playlistconfig | integer |     auto     |   Script mit den Style für die HTML-Tabelle   |

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
