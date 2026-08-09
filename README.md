[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#6-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#3-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#3-spenden)

# Symcon-Modul: Kodi  <!-- omit in toc -->

Implementierung der Kodi JSON-RPC API in IP-Symcon.

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [1. Vorbemerkungen](#1-vorbemerkungen)
  - [Zur Library](#zur-library)
  - [Zur Integration von Geräten](#zur-integration-von-geräten)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Enthaltende Module](#4-enthaltende-module)
  - [Kodi Discovery:](#kodi-discovery)
  - [Kodi Konfigurator:](#kodi-konfigurator)
  - [Kodi Splitter:](#kodi-splitter)
  - [Kodi Addons:](#kodi-addons)
  - [Kodi Application:](#kodi-application)
  - [Kodi Audio Datenbank:](#kodi-audio-datenbank)
  - [Kodi Favoriten:](#kodi-favoriten)
  - [Kodi Files:](#kodi-files)
  - [Kodi GUI:](#kodi-gui)
  - [Kodi Input:](#kodi-input)
  - [Kodi Player:](#kodi-player)
  - [Kodi Playlist:](#kodi-playlist)
  - [Kodi PVR:](#kodi-pvr)
  - [Kodi Settings:](#kodi-settings)
  - [Kodi System:](#kodi-system)
  - [Kodi Video Datenbank:](#kodi-video-datenbank)
- [5. Anhang](#5-anhang)
  - [1. GUID der Module](#1-guid-der-module)
  - [2. Changelog](#2-changelog)
  - [3. Spenden](#3-spenden)
- [6. Lizenz](#6-lizenz)

## 1. Funktionsumfang

Ermöglicht das Steuern und das Empfangen von Statusänderungen von der Mediacenter-Software Kodi über das Netzwerk.  
Direkte Bedienung im WebFront möglich.  
Abbilden fast der gesamten Kodi-API im vollen Funktionsumfang in PHP-Befehlen für eigene Scripte in IPS.

## 1. Vorbemerkungen

### Zur Library

Die Kodi Library implementiert die Kodi JSON-RPC API in IP-Symcon. Sie ermöglicht die Steuerung und Überwachung von Kodi-Medienzentren über das Netzwerk. Die Bibliothek ist modular aufgebaut und besteht aus folgenden Komponenten:

- **KodiSplitter** – Zentrale Kommunikationsinstanz für die JSON-RPC-Verbindung zu Kodi
- **KodiDevice-Module** – Jeweils eine Instanz pro Funktionsbereich der Kodi-API (z. B. Player, Playlist, System)
- **KodiDiscovery** – Automatische Erkennung von Kodi-Geräten im Netzwerk per mDNS
- **KodiConfigurator** – Unterstützung bei der Einrichtung der Geräte-Instanzen

### Zur Integration von Geräten

Die Integration eines Kodi-Geräts erfolgt in mehreren Schritten:

1. **Discovery** – Die Discovery-Instanz findet Kodi-Geräte im lokalen Netzwerk automatisch.
2. **Konfigurator** – Der Konfigurator legt alle benötigten Instanzen für ein gefundenes Gerät an.
3. **Splitter + IO** – Der Splitter stellt die Verbindung zum Kodi JSON-RPC Server her und verteilt Events an die Device-Instanzen.
4. **Device-Instanzen** – Jede Device-Instanz bildet einen Funktionsbereich der Kodi-API ab und stellt Statusvariablen sowie Aktionsmöglichkeiten bereit.

In den Kodi-Systemen müssen vorab folgende Einstellungen vorgenommen werden:

- In Einstellungen/Dienste/Steuerung/Webserver
   - Steuerung über HTTP erlauben.
   - Username und Password nach eigenem Ermessen.
- In Einstellungen/Dienste/Steuerung/Anwendungskontrolle
   - Fernsteuerung durch Anwendungen anderer Rechner erlauben.
- In Einstellungen/Dienste/UpnP/DLNA
   - UPnP-Support aktivieren.

> **Hinweis:** Damit Geräte über die Discovery-Instanz gefunden werden können, müssen bei gerouteten Netzen und bei NAT-Systemen Multicast-Pakete korrekt weitergeleitet werden.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Kodi-Installation auf einem unterstützten System

## 3. Software-Installation

Über den 'Module-Store' in IPS das Modul 'Kodi' hinzufügen.  
**Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**

## 4. Enthaltende Module

### [Kodi Discovery:](KodiDiscovery/)

- Automatische Erkennung von Kodi-Geräten im Netzwerk per mDNS / SSDP.
- Einfaches Einrichten von Konfiguratoren für gefundene Kodi-Instanzen.

### [Kodi Konfigurator:](KodiConfigurator/)

- Auslesen und Darstellen aller in Kodi und IPS bekannten Geräte und Instanzen.
- Einfaches Anlegen und Konfigurieren von neuen Geräte-Instanzen in IPS.

### [Kodi Splitter:](KodiSplitter/)

- Zentrale Kommunikationsinstanz für die JSON-RPC-Verbindung zu Kodi.
- Verwaltung der TCP-Verbindung, KeepAlive und Watchdog.

### [Kodi Addons:](KodiDeviceAddons/)

- Addons de-/aktivieren, lesen, visualisieren und ausführen.

### [Kodi Application:](KodiDeviceApplication/)

- Steuerung der Lautstärke, Stummschaltung, Software beenden und Statusanzeige.

### [Kodi Audio Datenbank:](KodiDeviceAudioLibrary/)

- Audio-Datenbank lesen, Künstler/Alben/Songs abfragen, Scannen und Bereinigen.

### [Kodi Favoriten:](KodiDeviceFavorites/)

- Favoriten lesen, visualisieren und ausführen.

### [Kodi Files:](KodiDeviceFiles/)

- Medienquellen, Verzeichnisse und Datei-Eigenschaften auslesen.

### [Kodi GUI:](KodiDeviceGUI/)

- Aktuelles Fenster, Steuerung, Skin, Vollbildmodus und Benachrichtigungen verwalten.

### [Kodi Input:](KodiDeviceInput/)

- Tastendrücke, Aktionen und Texteingaben an Kodi senden.

### [Kodi Player:](KodiDevicePlayer/)

- Playerstatus und aktuelle Wiedergabe steuern und visualisieren.

### [Kodi Playlist:](KodiDevicePlaylist/)

- Playlists beschreiben, lesen, bearbeiten und visualisieren.

### [Kodi PVR:](KodiDevicePVR/)

- PVR-Kanäle, Aufnahmen und Timer verwalten sowie Kanalsuche ausführen.

### [Kodi Settings:](KodiDeviceSettings/)

- Einstellungen von Kodi lesen, schreiben und verwalten.

### [Kodi System:](KodiDeviceSystem/)

- Systemzustand steuern (Ein/Aus, Neustart, Standby) und optische Laufwerke verwalten.

### [Kodi Video Datenbank:](KodiDeviceVideoLibrary/)

- Video-Datenbank lesen, Filme/Serien/Musikvideos abfragen, Scannen und Bereinigen.

## 5. Anhang

### 1. GUID der Module

|        Instanz         |     Typ      |  Prefix  |                  GUID                  |
| :--------------------: | :----------: | :------: | :------------------------------------: |
|     KodiDiscovery      |  Discovery   |   KODI   | {28AC8A6C-4E03-43BE-9C3E-B8FEF78D374C} |
|      KodiSplitter      |   Splitter   |   KODI   | {D2F106B5-4473-4C19-A48F-812E8BAA316C} |
|    KodiConfigurator    | Konfigurator |   KODI   | {7B4F8B62-7AB4-4877-AD60-F3B294DDB43E} |
|    KodiDeviceAddons    |    Device    | KODIADD  | {0731DD94-99E6-43D8-9BE3-2854B0C6EF24} |
| KodiDeviceApplication  |    Device    | KODIAPP  | {3AF936C4-9B31-48EC-84D8-A30F0BEF104C} |
| KodiDeviceAudioLibrary |    Device    | KODIAUD  | {AA078FB4-30C1-4EF1-A2DE-5F957F58BDDC} |
|  KodiDeviceFavorites   |    Device    | KODIFAV  | {DA2C90A2-3863-4454-9B07-FBD083420E10} |
|    KodiDeviceFiles     |    Device    | KODIFIL  | {54827867-BB3B-4ACC-A453-7A8D4DC78130} |
|     KodiDeviceGUI      |    Device    | KODIGUI  | {E15F2C11-0B28-4CFB-AEE6-463BD313A964} |
|    KodiDeviceInput     |    Device    | KODIINP  | {9F3BE8BB-4610-49F4-A41A-40E14F641F43} |
|    KodiDevicePlayer    |    Device    | KODIPLA  | {BA014AD9-9568-4F12-BE31-17D37BFED06D} |
|   KodiDevicePlaylist   |    Device    | KODIlist | {7D73D0FF-0CC7-43D0-A196-0D6143E52756} |
|     KodiDevicePVR      |    Device    | KODIPVR  | {9D73D46E-7B80-4814-A7B2-31768DC6AB7E} |
|   KodiDeviceSettings   |    Device    | KODISET  | {310E1F8B-D1B2-460C-9DBB-C7C500B28658} |
|    KodiDeviceSystem    |    Device    | KODISYS  | {03E18A60-02FD-45E8-8A2C-1F8E247C92D0} |
| KodiDeviceVideoLibrary |    Device    | KODIVID  | {07943DF4-FAB9-454F-AA9E-702A5F9C9D57} |

### 2. Changelog

**Version 3.02:**  
- Fix: WOL-Port in `KodiDeviceSystem` korrigiert.  
- Fix: Absturz der Discovery-Instanz verhindert, wenn keine mDNS-Instanz vorhanden ist.  
- Fix: Docker-Erkennung in der Discovery-Instanz korrigiert.  

**Version 3.00:**  
- Change: Positionsvariable (seek) ist jetzt float, die VariableID ändert sich beim Update!  
- Fix: Fehlermeldungen beim Anspringen einer Position (seek).  
- Fix: Position des aktuellen Titels war in der Playlist falsch, wenn Shuffle aktiviert/deaktiviert wurde.  
- Fix: PVR-Instanz hat versucht Daten abzufragen, auch wenn kein PVR-Addon aktiv war.  
- Neu: Statusvariablen nutzen die neuen Profile von Symcon.  
- Neu: Eingebautes WOL und alternativ eigene Aktion, um Hardware einzuschalten.  
- Neu: Diverse Konfigurationsformulare überarbeitet und mit Dynamik versehen.  
- Neu: Settings-Instanz unterstützt mehr Typen und somit mehr Statusvariablen.  
- Neu: Discovery-Instanz zeigt Hinweis bei Nutzung von Docker + NAT an.  
- Fix: Fehlermeldung, wenn Logindaten im Splitter nicht korrekt sind.  

**Version 2.98:**  
- Fix: `KODIVIDEOLIB_GetMovieDetails` war defekt.  
- Fix: Verhalten des Splitters bei Verbindungsauf-/abbau und Konfigurationsänderungen verbessert.  

**Version 2.97:**  
- Fix: ClientSocket wollte immer Änderungen übernehmen, auch wenn keine da waren.  
- Fix: Verbindungstimeout beim Prüfen der Verfügbarkeit von Kodi auf 2000ms erhöht.  
- Fix: Änderungen des Hosts im ClientSocket wurden nicht erkannt.  
- Fix: Fehlermeldung beim Verbindungsaufbau, wenn Kodi nicht geantwortet hat.  

**Version 2.96:**  
- Neu: `KodiDeviceSettings`-Instanz.  

**Version 2.90:**  
- Neu: Discovery kann auch mit Hostnamen umgehen.  
- Fix: Links zur Dokumentation korrigiert.  
- Neu: Testcenter ergänzt.  
- Neu: Neue Statusvariablen nutzen englische Namen.  
- Neu: Übersetzungen der Statusvariablen.  
- Neu: Profile nutzen englische Namen.  
- Neu: Übersetzungen der Profile.  
- Fix: Fehlende Übersetzungen der Konfiguration ergänzt.  
- Neu: Übersetzungen der Fehlermeldungen.  
- Neu: Konfigurationsformulare optisch angepasst.  
- Fix: Config-Scripte werden nur beim Erzeugen initial versteckt.  
- Fix: Diverse Schreibfehler korrigiert.  
- Neu: Alle Webhooks der HTML-Tabellen werden mit einem Hash abgesichert.  

**Version 2.17:**  
- Fix: Fehlermeldung im Debug, wenn Splitter mehr als 256KB Daten hat.  
- Fix: RPC-Funktionen mit Punkt im Namen wurden nicht korrekt verarbeitet.  
- Fix: RPC-Namespace 'Other' wird ignoriert, dieser liefert keine verwertbaren Daten.  

**Version 2.16:**  
- Fix: Fehler in Debug-Ausgabe behoben.  

**Version 2.15:**  
- Fix: Splitter verlor die Verbindung, wenn Kodi mehr als 256KB Daten sendet. Daten dieser Größe werden jetzt verworfen.  
- Fix: Input-Device sendet Kommandos jetzt direkt, damit beim Empfang großer Daten kein Timeout auftritt.  
- Fix: Discovery-Instanz sucht nicht mehr selbstständig nach neuen Geräten.  

**Version 2.10:**  
- Fix: Fehlermeldung VariablenProfil in `KodiDevicePlaylist`.  
- Fix: Traits in neuen Namespace überführt.  
- Neu: Strict-Types.  

**Version 2.05:**  
- Neu: Konfigurator meldet, wenn IO nicht aktiv ist.  

**Version 2.01:**  
- Fix: HTTP-Port 80 wurde von der Discovery-Instanz nicht erkannt.  

**Version 2.0:**  
- Fix: Fehler beim Starten eines Favoriten über das WebFront.  
- Fix: Player-Instanz hat Variablen nicht aktualisiert, wenn der Player von Stop/Pause mit Resume fortgesetzt wurde.  
- Fix: Fehlende Übersetzungen ergänzt.  
- Fix: Fehlende Screenshots ergänzt.  
- Neu: Kodi Discovery im lokalen Netzwerk.  
- Neu: Kodi Konfigurator nutzt Listen.  

**Version 1.6:**  
- Fix: Splitter war bei IPS Neustart im falschen Zustand (verbunden), auch wenn Kodi nicht erreichbar war.  

**Version 1.5:**  
- Fix: Fehler im Timer der PVR-Instanz, wenn Kodi nicht erreichbar war.  
- Fix: Fehler in Playlist bei IPS 5.0.  

**Version 1.4:**  
- Fix: Für IPS 5.0.  

**Version 1.3:**  
- Fix: Konfig-Formular für Input-Modul war nicht übersetzt.  
- Fix: Konfig-Formular für Input-Modul fehlte eine Checkbox.  

**Version 1.2:**  
- Fix: Timer in Create verschoben.  
- Fix: PlayerModul hat IPS Start verhindert.  
- Neu: Input-Modul hat ein Eingabefeld im WebFront, um Zeichenfolgen an Kodi zu senden.  

**Version 1.1:**  
- Diverse Bugfixes.  
- Horizontale Remote hinzugefügt.  
- PVR zeigt TV & Radio-Kanäle sowie Aufzeichnungen an und kann diese wiedergeben.  
- Rückkanal für HTML-Tabellen hinzugefügt.  

**Version 1.0:**  
- Erstes offizielles Release.  

### 3. Spenden

Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

PayPal:  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)

Wunschliste:  
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 6. Lizenz

IPS-Modul:  
[Custom NC-SA](LICENSE)