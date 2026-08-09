[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#10-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#2-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#2-spenden)

# Kodi Application (KodiDeviceApplication)  <!-- omit in toc -->

Ermöglicht die Steuerung sowie die Darstellung von Lautstärke, Stummschaltung und Anwendungsstatus von Kodi in IP-Symcon.

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

- Steuerung der Lautstärke (setzen/abfragen).
- Stummschaltung aktivieren/deaktivieren.
- Anzeige der Kodi-Version und Name.
- Beenden der Kodi-Anwendung.

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

![Konfigurationsseite](imgs/conf1.png)

| Name | Eigenschaft | Typ | Standardwert | Funktion |
| :--- | :--- | :---: | :--- | :--- |
| Zeige Name | showName | bool | true | Statusvariable für Name verwenden |
| Zeige Version | showVersion | bool | true | Statusvariable für Version verwenden |
| Zeige Beenden | showExit | bool | true | Aktions-Variable für Beenden anlegen |

## 5. Statusvariablen

Folgende Statusvariablen werden automatisch angelegt.

| Name | Typ | Ident | Beschreibung |
| :--- | :---: | :--- | :--- |
| Lautstärke | int | Volume | Lautstärke (0 - 100) |
| Stummschaltung | bool | Mute | Stummschaltung aktiv / inaktiv |
| Name | string | Name | Name der Kodi-Anwendung |
| Version | string | Version | Version der Kodi-Anwendung |

## 6. Visualisierung

### Kachel Visualisierung

*Keine Kachel-Visualisierung vorhanden.*

### WebFront Visualisierung

![Application WebFront](imgs/wf1.png)

## 7. PHP-Befehlsreferenz

Für alle Befehle gilt:  
Tritt ein Fehler auf, wird eine Warnung erzeugt.

```php
bool KODIAPP_SetMute(int $InstanzID, bool $Value)
```

De-/Aktiviert die Stummschaltung.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIAPP_SetVolume(int $InstanzID, int $Value)
```

Setzt die Lautstärke (0 - 100).  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIAPP_Quit(int $InstanzID)
```

Beendet die Kodi-Anwendung.  
Liefert `true` bei Erfolg, sonst `false`.

---

```php
bool KODIAPP_RequestState(int $InstanzID, string $Ident)
```

Fordert den Wert einer Statusvariable an.  
Es ist der Ident der Statusvariable zu übergeben (`Volume`, `Mute`, `Name`, `Version`).  
Liefert `true` bei Erfolg, sonst `false`.

## 8. Aktionen

**Grundsätzlich können alle bedienbaren Statusvariablen als Ziel einer [`Aktion`](https://www.symcon.de/service/dokumentation/konzepte/automationen/ablaufplaene/aktionen/) mit `Auf Wert schalten` angesteuert werden, so dass hier keine speziellen Aktionen benutzt werden müssen.**

- Lautstärke über Slider anpassbar
- Stummschaltung über Checkbox
- Beenden über Button

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