[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Player <!-- omit in toc -->  

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

Die Installation erfolgt über den Module Store von IP-Symcon oder automatisch über den KodiConfigurator.

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDevicePlayer)
3. Player-Typ auswählen (0=Audio, 1=Video, 2=Bilder)
4. Mit übergeordnetem KodiSplitter verbinden

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
array|boolean KODIPLAYER_GetItem(integer $InstanzeID);
boolean KODIPLAYER_GoToNext(integer $InstanzeID);
boolean KODIPLAYER_GoToPrevious(integer $InstanzeID);
boolean KODIPLAYER_GoToTrack(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_LoadAlbum(integer $InstanzeID, integer $AlbumId);
boolean KODIPLAYER_LoadArtist(integer $InstanzeID, integer $ArtistId);
boolean KODIPLAYER_LoadChannel(integer $InstanzeID, integer $ChannelId);
boolean KODIPLAYER_LoadDirectory(integer $InstanzeID, string $Directory);
boolean KODIPLAYER_LoadDirectoryRecursive(integer $InstanzeID, string $Directory);
boolean KODIPLAYER_LoadEpisode(integer $InstanzeID, integer $EpisodeId);
boolean KODIPLAYER_LoadFile(integer $InstanzeID, string $File);
boolean KODIPLAYER_LoadGenre(integer $InstanzeID, integer $GenreId);
boolean KODIPLAYER_LoadMovie(integer $InstanzeID, integer $MovieId);
boolean KODIPLAYER_LoadMusicVideo(integer $InstanzeID, integer $MusicvideoId);
boolean KODIPLAYER_LoadPlaylist(integer $InstanzeID);
boolean KODIPLAYER_LoadRecording(integer $RecordingId);
boolean KODIPLAYER_LoadSong(integer $InstanzeID, integer $SongId);
boolean KODIPLAYER_Pause(integer $InstanzeID);
boolean KODIPLAYER_Play(integer $InstanzeID);
boolean KODIPLAYER_SetAudioStream(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_SetPartymode(integer $InstanzeID, boolean $Value);
boolean KODIPLAYER_SetPosition(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_SetRepeat(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_SetShuffle(integer $InstanzeID, boolean $Value);
boolean KODIPLAYER_SetSpeed(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_SetSubtitle(integer $InstanzeID, integer $Value);
boolean KODIPLAYER_Stop(integer $InstanzeID);
```

## 8. Aktionen

- Wiedergabe steuern über Buttons
- Position in der Wiedergabe anpassen
- Audiospur und Untertitel auswählen

## 9. Anhang

### Eigenschaften

| Eigenschaft |   Typ   | Standardwert |                              Funktion                               |
| :---------: | :-----: | :----------: | :-----------------------------------------------------------------: |
|  PlayerID   | integer |      0       |            Playermodus: 0 = Audio, 1 = Video, 2 = Bilder            |
|  CoverSize  | integer |     300      |  Die Höhe vom Cover in Pixel auf welche das 'Cover' skaliert wird   |
|  CoverTyp   | string  |    thumb     | Varianten: 'thumb', 'album', 'artist', 'fanart', 'poster', 'banner' |

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
