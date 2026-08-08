[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi PVR (KodiDevicePVR)  <!-- omit in toc -->

Ermöglicht das Verwalten, Steuern und Darstellen von Live-TV/Radio, Aufnahmen und Timern von Kodi in IP-Symcon.

## Inhaltsverzeichnis  <!-- omit in toc -->

- [1. Funktionsumfang](#1-funktionsumfang)
- [2. Voraussetzungen](#2-voraussetzungen)
- [3. Software-Installation](#3-software-installation)
- [4. Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
- [5. Statusvariablen](#5-statusvariablen)
- [6. Visualisierung](#6-visualisierung)
  - [Kachel Visualisierung](#kachel-visualisierung)
  - [WebFront Visualisierung](#webfront-visualisierung)
- [7. PHP-Befehlsreferenz](#7-php-befehlsreferenz)
- [8. Aktionen](#8-aktionen)
- [9. Anhang](#9-anhang)
  - [1. Changelog](#1-changelog)
  - [2. Spenden](#2-spenden)
- [10. Lizenz](#10-lizenz)

## 1. Funktionsumfang

- Verwaltung und Anzeige von TV- und Radio-Kanälen.
- Auslesen und Wiedergeben von Aufnahmen.
- Steuern und Überwachen von Aufnahmen und Timern.
- Kanalsuche / Scan ausführen.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät
- Aktiviertes PVR-Addon in Kodi

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

Eine einfache Einrichtung ist über den Konfigurator [Kodi Konfigurator](../KodiConfigurator/README.md) möglich.  
Bei der manuellen Einrichtung ist die Instanz im Dialog `Instanz hinzufügen` unter dem Hersteller `Kodi` zu finden.

**Konfigurationsseite:**

| Name | Eigenschaft | Typ | Standardwert | Funktion |
| :--- | :--- | :---: | :--- | :--- |
| Zeige PVR-Verfügbarkeit | showIsAvailable | bool | true | Statusvariable für Verfügbarkeit erzeugen |
| Zeige Aufnahmestatus | showIsRecording | bool | true | Statusvariable für aktive Aufnahme erzeugen |
| Zeige Aufnahme-Aktion | showDoRecording | bool | true | Aktionsvariable zur Aufnahmesteuerung erzeugen |
| Zeige Suchlauf-Status | showIsScanning | bool | true | Statusvariable für Kanalsuchlauf erzeugen |
| Zeige TV-Kanalliste | showTVChannellist | bool | true | HTML-Tabelle mit TV-Kanälen erzeugen |
| Max. TV-Kanäle | showMaxTVChannels | int | 20 | Maximale Anzahl darzustellender TV-Kanäle |
| Zeige Radio-Kanalliste | showRadioChannellist | bool | true | HTML-Tabelle mit Radio-Kanälen erzeugen |
| Max. Radio-Kanäle | showMaxRadioChannels | int | 20 | Maximale Anzahl darzustellender Radio-Kanäle |
| Zeige Aufnahmeliste | showRecordinglist | bool | true | HTML-Tabelle mit Aufnahmen erzeugen |
| Max. Aufnahmen | showMaxRecording | int | 20 | Maximale Anzahl darzustellender Aufnahmen |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| PVR Verfügbar | bool | IsAvailable | PVR-Dienst verfügbar / nicht verfügbar |
| Aufnahme aktiv | bool | IsRecording | Aktuelle Aufnahme aktiv / inaktiv |
| Suchlauf aktiv | bool | IsScanning | Kanalsuchlauf aktiv / inaktiv |

## 6. Visualisierung

### Kachel Visualisierung

Darstellung als Kachel:  
![PVR](imgs/PVR.png)

### WebFront Visualisierung

![PVR WebFront](imgs/PVR_WF.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.

```php
bool KODIPVR_Scan(int $InstanzID)
```

Startet den PVR-Kanalsuchlauf.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIPVR_Record(int $InstanzID, bool $Record, string $Channel)
```

Startet (`$Record = true`) oder stoppt (`$Record = false`) eine Aufnahme auf dem angegebenen Kanal.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
array|bool KODIPVR_GetBroadcasts(int $InstanzID, int $ChannelId)
```

Liefert alle Sendungen eines Kanals als Array.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIPVR_GetChannels(int $InstanzID, string $ChannelTyp)
```

Liest alle Kanäle vom Typ `$ChannelTyp` (`tv` oder `radio`) aus.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIPVR_GetRecordings(int $InstanzID)
```

Liefert alle PVR-Aufnahmen als Array.  
Liefert ein Array bei Erfolg, sonst `false`.

---

```php
array|bool KODIPVR_GetTimers(int $InstanzID)
```

Liefert alle programmierten Aufnahme-Timer als Array.  
Liefert ein Array bei Erfolg, sonst `false`.

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

- Kanäle umschalten
- Aufnahmen starten/stoppen
- Timer verwalten

## 9. Anhang

### 1. Changelog

[Changelog der Library](../README.md#2-changelog)

### 2. Spenden

Die Library ist für die nicht kommerzielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:

PayPal:  
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)

Wunschliste:  
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

IPS-Modul:  
[Custom NC-SA](../LICENSE)