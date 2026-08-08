[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi Eingabesteuerung (KodiDeviceInput)  <!-- omit in toc -->

Ermöglicht das Senden von Tastendrücken, Aktionen und Texteingaben an Kodi in IP-Symcon.

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

- Senden von Navigations- und Steuerungstastendrücken an Kodi.
- Senden von Freitext-Eingaben an Kodi-Eingabefelder.
- Anzeige eines virtuellen Remote-Bedienfeldes.
- Erkennung, ob Kodi aktuell eine Eingabe erwartet.

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

Eine einfache Einrichtung ist über den Konfigurator [Kodi Konfigurator](../KodiConfigurator/README.md) möglich.  
Bei der manuellen Einrichtung ist die Instanz im Dialog `Instanz hinzufügen` unter dem Hersteller `Kodi` zu finden.

**Konfigurationsseite:**

| Name | Eigenschaft | Typ | Standardwert | Funktion |
| :--- | :--- | :---: | :--- | :--- |
| Zeige SVG Remote | showSVGRemote | bool | true | SVG-Fernbedienung anzeigen |
| Remote-Layout | RemoteId | int | 1 | 1 = vertikal, 2 = horizontal |
| Zeige Navigationsbuttons | showNavigationButtons | bool | true | Navigationsschaltflächen anzeigen |
| Zeige Steuerungbuttons | showControlButtons | bool | true | Steuerungsschaltflächen anzeigen |
| Zeige Eingabeaufforderung | showInputRequested | bool | true | Statusvariable bei ausstehender Eingabe anzeigen |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| Eingabe erforderlich | bool | InputRequested | Zeigt an, ob Kodi eine Texteingabe erwartet |

## 6. Visualisierung

### Kachel Visualisierung

Darstellung als Kachel:  
![Input](imgs/Input.png)

### WebFront Visualisierung

![Input WebFront](imgs/Input_WF.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.

```php
bool KODIINPUT_Up(int $InstanzID)
bool KODIINPUT_Down(int $InstanzID)
bool KODIINPUT_Left(int $InstanzID)
bool KODIINPUT_Right(int $InstanzID)
```

Sendet Navigationsbefehle (hoch, runter, links, rechts).  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIINPUT_Select(int $InstanzID)
bool KODIINPUT_ContextMenu(int $InstanzID)
bool KODIINPUT_Home(int $InstanzID)
bool KODIINPUT_Info(int $InstanzID)
bool KODIINPUT_ShowOSD(int $InstanzID)
bool KODIINPUT_ShowCodec(int $InstanzID)
```

Sendet Steuerungsbefehle an Kodi.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIINPUT_ExecuteAction(int $InstanzID, string $Action)
```

Sendet die in `$Action` übergebene Kodi-Aktion (z. B. `back`, `fullscreen`, `playpause`).  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIINPUT_SendText(int $InstanzID, string $Text, bool $Done)
```

Sendet den in `$Text` enthaltenen Text an Kodi. Mit `$Done = true` wird die Eingabe abgeschlossen.  
Liefert `true` bei Erfolg, sonst `false`.

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

- Navigation über virtuelle Fernbedienung
- Texteingabe über WebFront-Dialog
- Direkte Ausführung von Aktionen

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