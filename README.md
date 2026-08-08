[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Library<!-- omit in toc -->

Implementierung der Kodi JSON-RPC API in IP-Symcon.

## Inhaltsverzeichnis <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Vorbemerkungen](#2-vorbemerkungen)
  - [Zur Library](#zur-library)
  - [Zur Integration von Geräten](#zur-integration-von-geraten)
- [3. Voraussetzungen](#3-voraussetzungen)
- [4. Software-Installation](#4-software-installation)
- [5. Enthaltende Module](#5-enthaltende-module)
- [6. Einrichten der Instanzen in IPS](#6-einrichten-der-instanzen-in-ips)
- [7. Statusvariablen](#7-statusvariablen)
- [8. Funktionen der Instanzen](#8-funktionen-der-instanzen)
- [10. Aktionen](#10-aktionen)
- [11. Anhang](#11-anhang)
  - [1. GUID der Module](#1-guid-der-module)
  - [3. Changelog](#3-changelog)
  - [4. Spenden](#4-spenden)
- [12. Lizenz](#12-lizenz)

## 1. Funktionsumfang

 Ermöglicht das Steuern und das empfangen von Statusänderungen, von der Mediacenter-Software Kodi über das Netzwerk.
 Direkte Bedienung im WebFront möglich.
 Abbilden fast der gesamten Kodi-API in vollen Funktionsumfangs in PHP-Befehlen für eigene Scripte in IPS.

## 2. Vorbemerkungen

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
   - Username und Password nach eigenen Ermessen.
- In Einstellungen/Dienste/Steuerung/Anwendungskontrolle
   - Fernsteuerung durch Anwendungen anderer Rechner erlauben.
- In Einstellungen/Dienste/UpnP/DLNA
   - UPnP-Support aktivieren.

> **Hinweis:** Damit Geräte über die Discovery-Instanz gefunden werden können, müssen bei gerouteten Netzen und bei NAT-Systemen Multicast-Pakete korrekt weitergeleitet werden.

## 3. Voraussetzungen

 - Symcon ab Version 9.0
 - Kodi installation auf einem unterstützen System

## 4. Software-Installation

   Bei privater Nutzung:
   * Über den Module Store das 'KODI'-Modul installieren.

   **Bei kommerzieller Nutzung (z.B. als Errichter oder Integrator) wenden Sie sich bitte an den Autor.**

## 5. Enthaltende Module

|         Modul          |     Typ      |                            Funktion                            |
| :--------------------: | :----------: | :------------------------------------------------------------: |
|      KodiSplitter      |   Splitter   |      JSON-RPC-Verbindung zu Kodi, KeepAlive und Watchdog       |
|    KodiConfigurator    | Konfigurator |            Einfache Erstellung aller Kodi-Instanzen            |
|     KodiDiscovery      |  Discovery   |      Automatische Erkennung von Kodi-Geräten im Netzwerk       |
|    KodiDeviceAddons    |    Gerät     |   Addons de-/aktivieren, lesen, visualisieren und ausführen    |
| KodiDeviceApplication  |    Gerät     |          Lautstärke, Stummschaltung, Software beenden          |
| KodiDeviceAudioLibrary |    Gerät     |         Audio-Datenbank lesen, Scannen und Bereinigen          |
|  KodiDeviceFavourites  |    Gerät     |          Favoriten lesen, visualisieren und ausführen          |
|    KodiDeviceFiles     |    Gerät     |          Quellen, Verzeichnisse und Dateien auslesen           |
|     KodiDeviceGUI      |    Gerät     |  Fenster, Steuerung, Skin, Vollbildmodus, Benachrichtigungen   |
|    KodiDeviceInput     |    Gerät     |              Tastendrücke und Texteingaben senden              |
|    KodiDevicePlayer    |    Gerät     | Playerstatus und aktuelle Wiedergabe steuern und visualisieren |
|   KodiDevicePlaylist   |    Gerät     |         Playlists beschreiben, lesen und visualisieren         |
|     KodiDevicePVR      |    Gerät     |           PVR-Kanäle, Aufnahmen und Timer verwalten            |
|   KodiDeviceSettings   |    Gerät     |           Einstellungen von Kodi lesen und schreiben           |
|    KodiDeviceSystem    |    Gerät     |       Systemzustand steuern (Ein/Aus, Neustart, Standby)       |
| KodiDeviceVideoLibrary |    Gerät     |         Video-Datenbank lesen, Scannen und Bereinigen          |

## 6. Einrichten der Instanzen in IPS

  **Hinweis:**
  Damit Geräte über die Discovery-Instanz gefunden werden können, müssen bei in gerouteten Netzen und bei NAT Systemen Multicast-Pakete korrekt weitergeleitet werden.

  <span style="color:red">**Discovery funktioniert nicht in einem Docker Container welcher per NAT angebunden ist. Diese Konstellation wird aufgrund der fehlenden Multicast Fähigkeiten von Docker nicht unterstützt. In diesem Fall muss der Kodi Konfigurator manuell angelegt und konfiguriert werden.**</span>
  Für das Discovery werden Pakete über die Multicast-Adresse `239.255.255.250` auf Port `1900` gesendet und UDP Pakete auf Port `1901` empfangen.

---

  Es wird empfohlen die Einrichtung mit der Instanz Kodi-Discovery durchzuführen.

  - Im Objektbaum die Kategorie 'Discovery Instanzen' öffnen.
  - Auf die Instanz 'Kodi Discovery' doppelt klicken. ![Discovery öffnen](imgs/Discovery1.png)
  - Im folgenden Dialog sollten alle im lokalen Netzwerk verfügbaren Kodi-Systeme aufgelistet werden. ![Discovery](imgs/Discovery.png)
  - Im Dialog das gewünschte System auswählen und den Button 'Erstellen' betätigen.
  - Anschließend ändert sich der Button zu 'Konfigurieren' und muss erneut betätigt werden.
  - Im sich öffnenden Konfigurator alle gewünschten Funktionen wie zuvor erzeugen.
  - ![Instanzen erzeugen](imgs/Konfigurator.png)
  - Über den Button 'Gateway konfigurieren' kann die optionale HTTP-Authentifizierung sowie ein abweichender JSON-RPC Port konfiguriert werden.
  - In den jeweiligen Instanzen sind dann noch weitere Einstellungen nach eigenen Vorlieben einzustellen.

## 7. Statusvariablen

Alle Device-Instanzen legen automatisch Statusvariablen an, die den aktuellen Zustand des jeweiligen Kodi-Funktionsbereichs abbilden. Die Variablen werden anhand der RPC-Properties des jeweiligen Namespace erzeugt und über Events vom Kodi-Splitter aktuell gehalten.

|         Modul          | Statusvariablen (Beispiele)                                                         |
| :--------------------: | :---------------------------------------------------------------------------------- |
|    KodiDeviceAddons    | Addon-Liste, Enable-Status                                                          |
| KodiDeviceApplication  | Lautstärke (volume), Stummschaltung (muted), Name, Version                          |
| KodiDeviceAudioLibrary | Scan-Status, Clean-Status                                                           |
|  KodiDeviceFavourites  | Favoriten-Liste                                                                     |
|     KodiDeviceGUI      | Aktuelles Fenster, Aktuelle Steuerung, Skin, Vollbildmodus, Bildschirmschoner       |
|    KodiDeviceInput     | Eingabe erforderlich (InputRequested)                                               |
|    KodiDevicePlayer    | Wiedergabestatus (speed, time, percentage), Partymodus, Wiederholung, Zufall        |
|   KodiDevicePlaylist   | Playlist-Inhalt                                                                     |
|     KodiDevicePVR      | Verfügbarkeit, Aufnahme aktiv, Suchlauf aktiv                                       |
|   KodiDeviceSettings   | Abgefragte Einstellungen                                                            |
|    KodiDeviceSystem    | Power, Standby, Hibernate, Reboot, Shutdown, Eject Optical Drive, Low Battery Event |
| KodiDeviceVideoLibrary | Scan-Status, Clean-Status                                                           |

Die Statusvariablen werden über die RPC-Events (`OnPropertyChanged`, `OnAVStart`, `OnAVStop` usw.) automatisch aktualisiert. Zusätzlich können über die PHP-Funktion `*_RequestState` einzelne Werte manuell abgefragt werden.

## 8. Funktionen der Instanzen

Jeder Typ von Instanz bildet einen bestimmen Funktionsbereich der Kodi-API ab.

 **Kodi Addons (KodiDeviceAddons):**
 RPC-Namensraum : Addons

 Addons                 - de/aktivieren, lesen, visualisieren und ausführen.
  ![Addon](imgs/Addon.png)
  ![Addon WebFront](imgs/Addon_WF.png)

---

 **Kodi Anwendung (KodiDeviceApplication):**
 RPC-Namensraum : Application

 Lautstärke             - Setzen, lesen und visualisieren.
 Stummschaltung         - Setzen, lesen und visualisieren.
 Software beenden       - Nur ausführen.
 Namen der Software     - Lesen und visualisieren.
 Version der Software   - Lesen und visualisieren.
  ![Application](imgs/Application.png)
  ![Application WebFront](imgs/Application_WF.png)

---

 **Kodi Audio Datenbank (KodiDeviceAudioLibrary):**
 RPC-Namensraum : AudioLibrary

 Künstler   - Lesen von Daten aus der Datenbank.
 Alben      - Lesen von Daten aus der Datenbank.
 Songs      - Lesen von Daten aus der Datenbank.
 Datenbank  - Ausführen von Scan un Clean. Status visualisieren.

Das Setzen von Daten in der Datenbank ist nicht möglich!
  ![AudioLibrary](imgs/AudioLib.png)
  ![AudioLibrary WebFront](imgs/AudioLib_WF.png)

---

**Kodi Favoriten (KodiDeviceFavourites):**
 RPC-Namensraum : Favourites

 Favoriten   - Lesen, visualisieren und ausführen.

  ![Favourites](imgs/Favourites.png)
  ![Favourites WebFront](imgs/Favourites_WF.png)

---

 **Kodi Files (KodiDeviceFiles):**
 RPC-Namensraum : Files

 Quellen       - Lesen aller bekannten Medienquellen.
 Verzeichnisse - Auslesen von Verzeichnissen.
 Dateien       - Auslesen von Eigenschaften einer Datei.

---

 **Kodi GUI (KodiDeviceGUI):**
 RPC-Namensraum : GUI

 Aktuelles Fenster  - Lesen und visualisieren.
 Aktuelle Steuerung - Lesen und visualisieren.
 Aktueller Skin     - Lesen und visualisieren.
 Vollbildmodus      - Setzen, lesen und visualisieren.
 Bildschirmschoner  - Status visualisieren.
 Benachrichtungen   - Senden.

Hinweise zu den 'Window IDs' und 'Window Name' sind hier verfügbar:
[Kodi Website - Window IDs] (http://kodi.wiki/view/Window_IDs)
  ![GUI](imgs/GUI.png)
  ![GUI WebFront](imgs/GUI_WF.png)

---

 **Kodi Input (KodiDeviceInput):**
 RPC-Namensraum : Input

 Tastendruck    - Senden
 Text           - Senden

  ![Input](imgs/Input.png)
  ![Input WebFront](imgs/Input_WF.png)

---

 **Kodi Playerstatus (KodiDevicePlayer):**
 PRC-Namensraum : Player
 Hinweis: Jeder 'Player' (Audio,Video,Bilder) benötigt eine eigene Instanz.
 Player              - Setzen, lesen und visualisieren des Status.
 Aktuelle Wiedergabe - Setzen, lesen und visualisieren.

  ![Audio Player](imgs/AudioPlayer.png)
  ![Picture Player](imgs/PicturePlayer.png)
  ![Video Player](imgs/VideoPlayer.png)
  ![Audio Player WebFront](imgs/AudioPlayer_WF.png)
  ![Picture Player WebFront](imgs/PicturePlayer_WF.png)
  ![Video Player WebFront](imgs/VideoPlayer_WF.png)

---

 **Kodi Playlist (KodiDevicePlaylist):**
 PRC-Namensraum : Playlist
 Hinweis: Jeder 'Player' (Audio,Video,Bilder) hat immer eine eigene Playlist!

 Playlist           - Beschreiben, lesen und visualisieren.
 Player             - Direktes anspringen eines Eintrages.

  ![Audio Playlist](imgs/AudioPlaylist.png)
  ![Picture Playlist](imgs/PicturePlaylist.png)
  ![Video Playlist](imgs/VideoPlaylist.png)
  ![Audio Playlist WebFront](imgs/AudioPlaylist_WF.png)
  ![Picture Playlist WebFront](imgs/PicturePlaylist_WF.png)
  ![Video Playlist WebFront](imgs/VideoPlaylist_WF.png)

---

 **Kodi PVR (KodiDevicePVR):**
 RPC-Namensraum : PVR

 Verfügbarkeit      - Zustand lesen und visualisieren.
 Suchlauf           - Starten, Zustand lesen und visualisieren.
 Aufnahme           - Steuern, Zustand lesen und visualisieren.
 Kanäle & Gruppen   - Lesen und visualisieren (inkl. umschalten von Kanälen.)
 Aufnahmen          - Lesen und visualisieren (inkl. starten der Wiedergabe.)
 Timer              - Lesen
  ![PVR](imgs/PVR.png)
  ![PVR WebFront](imgs/PVR_WF.png)

---

 **Kodi Settings (KodiDeviceSettings):**
 RPC-Namensraum : Settings

  ![Settings](imgs/Settings.png)
  ![Settings WebFront](imgs/Settings_WF.png)

---

 **Kodi System (KodiDeviceSystem):**
 RPC-Namensraum : System

 Systemzustand  - Starten, Beenden, Status visualisieren.
 Optisches LW   - Auswerfen
  ![System](imgs/System.png)
  ![System WebFront](imgs/System_WF.png)

---

 **Kodi VideoLibrary (KodiDeviceVideoLibrary):**
 RPC-Namensraum : VideoLibrary

 Filme      - Lesen von Daten aus der Datenbank.
 Serien     - Lesen von Daten aus der Datenbank.
 Musikvideo - Lesen von Daten aus der Datenbank.
 Datenbank  - Ausführen von Scan und Clean. Status visualisieren.

Das Setzen von Daten in der Datenbank ist nicht möglich!
  ![VideoLibrary](imgs/VideoLib.png)
  ![VideoLibrary WebFront](imgs/VideoLib_WF.png)

---

 **Kodi Splitter (KodiSplitter):**
 RPC-Namensraum : JSONRPC
  ![Splitter](imgs/Splitter.png)

## 10. Aktionen

__Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit 'Auf Wert schalten' angesteuert werden, so das hier keine speziellen Aktionen benutzt werden müssen.__

Dennoch gibt es diverse Aktionen für die 'ONVIF Media Stream' Instanz.
Wenn so eine Instanz als Ziel einer Aktion ausgewählt wurde, stehen folgende Aktionen zur Verfügung:
![Aktionen](imgs/Actions.png)
//Todo

## 11. Anhang

###  1. GUID der Module

|        Instanz         |                  GUID                  |
| :--------------------: | :------------------------------------: |
|    KodiDeviceAddons    | {0731DD94-99E6-43D8-9BE3-2854B0C6EF24} |
| KodiDeviceApplication  | {3AF936C4-9B31-48EC-84D8-A30F0BEF104C} |
| KodiDeviceAudioLibrary | {AA078FB4-30C1-4EF1-A2DE-5F957F58BDDC} |
|  KodiDeviceFavourites  | {DA2C90A2-3863-4454-9B07-FBD083420E10} |
|    KodiDeviceFiles     | {54827867-BB3B-4ACC-A453-7A8D4DC78130} |
|     KodiDeviceGUI      | {E15F2C11-0B28-4CFB-AEE6-463BD313A964} |
|    KodiDeviceInput     | {9F3BE8BB-4610-49F4-A41A-40E14F641F43} |
|     KodiDevicePVR      | {9D73D46E-7B80-4814-A7B2-31768DC6AB7E} |
|    KodiDevicePlayer    | {BA014AD9-9568-4F12-BE31-17D37BFED06D} |
|   KodiDevicePlaylist   | {7D73D0FF-0CC7-43D0-A196-0D6143E52756} |
|   KodiDeviceSettings   | {310E1F8B-D1B2-460C-9DBB-C7C500B28658} |
|    KodiDeviceSystem    | {03E18A60-02FD-45E8-8A2C-1F8E247C92D0} |
| KodiDeviceVideoLibrary | {07943DF4-FAB9-454F-AA9E-702A5F9C9D57} |
|    KodiConfigurator    | {7B4F8B62-7AB4-4877-AD60-F3B294DDB43E} |
|      KodiSplitter      | {D2F106B5-4473-4C19-A48F-812E8BAA316C} |

### 3. Changelog

Version 3.02:
 - Fix: WOL-Port in `KodiDeviceSystem` korrigiert.
 - Fix: Absturz der Discovery-Instanz verhindert, wenn keine mDNS-Instanz vorhanden ist.
 - Fix: Docker-Erkennung in der Discovery-Instanz korrigiert.

Version 3.00:
 - <span style='color:red'><b>ÄNDERUNG: Positionsvariable (seek) ist jetzt float, die VariableID ändert sich beim Update!</b></span>
 - Fix: Fehlermeldungen beim anspringen einer Position (seek).
 - Fix: Position des aktuellen Titel war in der Playlist falsch, wenn Shuffle aktiviert/deaktiviert wurde.
 - Fix: PVR Instanz hat versucht Daten abzufragen, auch wenn kein PVR Addon aktiv war.
 - Neu: Statusvariablen nutzen die neuen Profile von Symcon.
 - Neu: Eingebautes WOL und alternativ eigene Aktion um Hardware einzuschalten.
 - Neu: Diverse Konfigurationsformulare überarbeitet und mit Dynamik versehen.
 - Neu: Settings Instanz unterstützt mehr Typen und somit mehr Statusvariablen.
 - Neu: Discovery Instanz zeigt Hinweis bei Nutzung von Docker + NAT an.
 - Fix: Fehlermeldung wenn Login Daten im Splitter nicht korrekt sind.

 Version 2.98:
  - Fix: KODIVIDEOLIB_GetMovieDetails war defekt.
  - Fix: Verhalten des Splitters bei Verbindungsauf/abbau und Konfigurationsänderungen verbessert.

Version 2.97:
  - Fix: ClientSocket wollte immer Änderungen übernehmen, auch wenn keine da waren.
  - Fix: Verbindungtimout beim prüfen der Verfügbarkeit von Kodi erhöht auf 2000ms.
  - Fix: Änderungen des Host im ClientSocket wurden nicht erkannt.
  - Fix: Fehlermeldung beim Verbindungsaufbau, wenn Kodi nicht geantwortet hat.

Version 2.96:
  - Neu: KodiDeviceSettings Instanz.

Version 2.90:
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
  - Fix: Config-Scripte werden nur beim erzeugen initial versteckt.
  - Fix: Diverse Schreibfehler korrigiert
  - Neu: Alle Webhooks der HTML-Tabellen werden mit einem Hash abgesichert.

Version 2.17:
  - Fix: Fehlermeldung im Debug wenn Splitter mehr als 256Kb Daten hat.
  - Fix: RPC-Funktionen mit 'Punkt' im Namen wurden nicht korrekt verarbeitet.
  - Fix: RPC-Namespace 'Other' wird ignoriert, dieser liefert keine verwertbaren Daten.

Version 2.16:
  - Fix: Fehler im Debug-Ausgabe behoben.

Version 2.15:
  - Fix: Splitter verlor die Verbindung, wenn Kodi mehr als 256kB Daten sendet. Daten dieser größe werden jetzt verworfen.
  - Fix: Input-Device sendet Kommandos jetzt direkt, damit beim empfang grosser Daten kein Timeout auftritt.
  - Fix: Discovery-Instanz sucht nicht mehr selbstständig nach neuen Geräten.

Version 2.10:
  - Fix: Fehlermeldung VariablenProfil in KodiDevicePlaylist.
  - Fix: Traits in neuen Namespace überführt.
  - Neu: Strict-Types.

Version 2.05:
  - Neu: Konfigurator meldet wenn IO nicht aktiv ist.

Version 2.01:
  - Fix: HTTP-Port 80 wurde von der Discovery-Instanz nicht erkannt.

 Version 2.0:
  - Fix: Fehler beim starten eines Favoriten über das WebFront.
  - Fix: Player Instanz hat Variablen nicht aktualisiert, wenn der Player von Stop/Pause mit Resume fortgesetzt wurde.
  - Fix: Fehlende Übersetzungen ergänzt.
  - Fix: Fehlende Screenshots ergänzt.
  - Neu: Kodi Discovery im lokalen Netzwerk.
  - Neu: Kodi Konfigurator nutzt Listen.

 Version 1.6:
  - Fix: Splitter war bei IPS Neustart im falschen Zustand (verbunden), auch wenn Kodi nicht erreichbar war.

 Version 1.5:
  - Fix: Fehler im Timer der PVR Instanz wenn KODI nicht erreichbar war
  - Fix: Fehler in Playlist bei IPS 5.0

 Version 1.4:
  - Fix: Für IPS 5.0

 Version 1.3:
  - Fix: Konfig-Formular für Input-Modul war nicht übersetzt
  - Fix: Konfig-Formular für Input-Modul fehlte eine Checkbox

 Version 1.2:
  - Fix: Timer in Create verschoben
  - Fix: PlayerModul hat IPS Start verhindert
  - Neu: Input-Modul hat ein Eingabefeld im WebFront um Zeichenfolgen an Kodi zu senden

 Version 1.1:
  - Diverse Bugfixes
  - Horizontale Remote hinzugefügt
  - PVR zeigt TV & Radio-Kanäle sowie Aufzeichnungen an und kann Diese wiedergeben
  - Rückkanal für HTML-Tabellen hinzugefügt

 Version 1.0:
  - Release

----------

### 4. Spenden

  Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

  PayPal:
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)

  Wunschliste:
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 12. Lizenz

[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)
