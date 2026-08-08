[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi System <!-- omit in toc -->

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

- Systemsteuerung (Ein/Aus, Neustart, Standby)
- Statusüberwachung
- Optisches Laufwerk steuern
- Batterie-Status (falls unterstützt)

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceSystem)
3. Mit übergeordnetem KodiSplitter verbinden
4. Power-On Methode konfigurieren

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| PowerOnType | integer | 1 | 0 = internes WOL, 1 = WOLAction ausführen |
| MACAddress | string | | MAC-Adresse für internes WOL |
| WOLAction | string | | Aktion welche zum einschalten ausgeführt werden soll |
| PowerOff | integer | 0 | Ausschalt-Methode: 0 = OFF, 1 = Hibernate, 2 = Standby |

## 5. Statusvariablen

- Power-Status
- Standby-Status
- Hibernate-Status
- Reboot-Status
- Low Battery Event

## 6. Visualisierung

![System](imgs/System.png)
![System WebFront](imgs/System_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODISYS_Power(integer $InstanzeID, boolean $Value);
```
 Schaltet Kodi ein (TRUE) oder aus (FALSE).
 Einschalten erfolgt per hinterlegten PHP-Script in der Instanz-Konfiguration.
 Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODISYS_WakeUp(integer $InstanzeID);
```
 Startet das hinterlegte Einschalt-Script um die Kodi-Hardware einzuschalten.
 Der Modus für das Ausschalten ist ebenfalls in der Instanz zu konfigurieren.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODISYS_Shutdown(integer $InstanzeID);
```
 Führt einen Shutdown auf Betriebssystemebene aus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODISYS_Hibernate(integer $InstanzeID);
```
 Führt einen Hibernate auf Betriebssystemebene aus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODISYS_Suspend(integer $InstanzeID);
```
 Führt einen Suspend auf Betriebssystemebene aus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODISYS_Reboot(integer $InstanzeID);
```
 Führt einen Reboot auf Betriebssystemebene aus.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

 ```php
boolean KODISYS_EjectOpticalDrive(integer $InstanzeID);
```
 Öffnet das Optische Laufwerk.
 Ein Schließen ist häufig nicht möglich!
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODISYS_RequestState(integer $InstanzeID, string $Ident);
```
 Frage einen Wert ab.
 Es können hier nur Fähigkeiten angefragt werden.
 Diese verstecken automatisch die Aktionsvariablen welche nicht verwendet werden können.
 $Ident kann "canshutdown", "canhibernate", "cansuspend" oder "canreboot" sein.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Systemzustand ändern über Buttons
- Optisches Laufwerk auswerfen

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
