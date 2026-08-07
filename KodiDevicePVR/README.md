[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi PVR <!-- omit in toc -->  

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

- Verwaltung von PVR-Kanälen, Aufnahmen und Timern
- Anzeige der aktuellen Sendungen
- Steuerung von Aufnahmen
- Suchfunktion für Kanäle

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Aktiviertes PVR-Addon in Kodi

## 3. Software-Installation

Die Installation erfolgt über den Module Store von IP-Symcon oder automatisch über den KodiConfigurator.

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDevicePVR)
3. Mit übergeordnetem KodiSplitter verbinden

## 5. Statusvariablen

- Verfügbarkeit des PVR-Dienstes
- Aktive Aufnahme
- Suchlauf-Status
- Aktuelle Sendung

## 6. Visualisierung

![PVR](imgs/PVR.png)
![PVR WebFront](imgs/PVR_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODIPVR_Scan();
boolean KODIPVR_Record(integer $InstanzeID, boolean $Record, string $Channel);
array|boolean KODIPVR_GetBroadcasts(integer $InstanzeID, integer $ChannelId);
array|boolean KODIPVR_GetBroadcastDetails(integer $InstanzeID, integer $BroadcastId);
array|boolean KODIPVR_GetChannels(integer $InstanzeID, string $ChannelTyp);
array|boolean KODIPVR_GetChannelDetails(integer $InstanzeID, integer $ChannelId);
array|boolean KODIPVR_GetChannelGroups(integer $InstanzeID, string $ChannelTyp);
array|boolean KODIPVR_GetChannelGroupDetails(integer $InstanzeID, integer $ChannelGroupId);
array|boolean KODIPVR_GetRecordings(integer $InstanzeID);
array|boolean KODIPVR_GetRecordingDetails(integer $InstanzeID, integer $RecordingId);
array|boolean KODIPVR_GetTimers(integer $InstanzeID);
array|boolean KODIPVR_GetTimerDetails(integer $InstanzeID, integer $TimerId);
```

## 8. Aktionen

- Kanäle umschalten
- Aufnahmen starten/stoppen
- Timer verwalten

## 9. Anhang

### Eigenschaften

|      Eigenschaft       |   Typ   | Standardwert |                         Funktion                         |
| :--------------------: | :-----: | :----------: | :------------------------------------------------------: |
|    showIsAvailable     | boolean |     true     |        Status-Variable PVR-Verfügbarkeit anzeigen        |
|    showIsRecording     | boolean |     true     |       Status-Variable Aufzeichnung aktiv anzeigen        |
|    showDoRecording     | boolean |     true     | Aktions-Variable zum steuern einer Aufzeichnung anzeigen |
|     showIsScanning     | boolean |     true     |      Status-Variable für aktive Kanalsuche anzeigen      |
|   showTVChannellist    | boolean |     true     |         HTML-Tabelle mit den TV Kanälen erzeugen         |
|   showMaxTVChannels    | integer |      20      |           Anzahl der darzustellenden TV Kanäle           |
|  TVChannellistconfig   | integer |     auto     |        Script mit den Style für die HTML-Tabelle         |
|      TVThumbSize       | integer |     100      |                  Breite der Senderlogos                  |
|  showRadioChannellist  | boolean |     true     |       HTML-Tabelle mit den Radio Kanälen erzeugen        |
|  showMaxRadioChannels  | integer |      20      |         Anzahl der darzustellenden Radio Kanäle          |
| RadioChannellistconfig | integer |     auto     |        Script mit den Style für die HTML-Tabelle         |
|     RadioThumbSize     | integer |     100      |                  Breite der Senderlogos                  |
|   showRecordinglist    | boolean |     true     |       HTML-Tabelle mit den Aufzeichnungen erzeugen       |
|    showMaxRecording    | integer |      20      |        Anzahl der darzustellenden Aufzeichnungen         |
|  Recordinglistconfig   | integer |     auto     |        Script mit den Style für die HTML-Tabelle         |
|   RecordingThumbSize   | integer |     100      |                  Breite der Thumbnails                   |

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
