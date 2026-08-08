[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Addons <!-- omit in toc -->

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

- Verwaltung von Kodi-Addons (Anzeige, Aktivierung/Deaktivierung)
- Ausführen von Addons mit optionalen Parametern
- Visualisierung der Addon-Liste im WebFront

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceAddons)
3. Mit übergeordnetem KodiSplitter verbinden

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| showAddonlist | boolean | true | HTML-Tabelle mit den Addons erzeugen |
| Addonlistconfig | integer | auto | Script mit den Style für die HTML-Tabelle |
| ThumbSize | integer | 100 | Breite der Thumbnails |

## 5. Statusvariablen

- Addon-Liste (HTML-Tabelle)
- Enable-Status der Addons

## 6. Visualisierung

![Addon](imgs/Addon.png)
![Addon WebFront](imgs/Addon_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODIADDONS_ExecuteAddon(integer $InstanzeID, string $AddonId);
```
 Startet das in $AddonId übergebene Addon.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIADDONS_ExecuteAddonWait(integer $InstanzeID, string $AddonId);
```
 Startet das in $AddonId übergebene Addon und wartet auf die Ausführung.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIADDONS_ExecuteAddonEx(integer $InstanzeID, string $AddonId, string $Param);
```
 Startet das in $AddonId übergebene Addon mit Parametern.
 Die zu übergebenden Parameter an das AddOn müssen als JSON-String in $Params übergeben werden.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIADDONS_ExecuteAddonExWait(integer $InstanzeID, string $AddonId, string $Param);
```
 Startet das in $AddonId übergebene Addon mit Parametern und wartet auf die Ausführung.
 Die zu übergebenden Parameter an das AddOn müssen als JSON-String in $Params übergeben werden.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
array|boolean KODIADDONS_GetAddonDetails(integer $InstanzeID, string $AddonId);
```
 Liest die Eigenschaften eines Addons aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurückgegeben.

|    Index     |   Typ   |  Beschreibung   |
| :----------: | :-----: | :-------------: |
|   addonid    | string  |                 |
|  disclaimer  | string  |                 |
|    fanart    | string  | Pfad zum Fanart |
|    broken    |  mixed  |                 |
|    author    | string  |                 |
|   enabled    | boolean |                 |
|  extrainfo   |  array  |                 |
|  thumbnail   | string  | Pfad zum Cover  |
|     path     | string  |                 |
| dependencies |  array  |                 |
|     type     |  array  |                 |
| description  | string  |  Beschreibung   |
|     name     | string  |                 |
|   version    | string  |                 |
|   summary    | string  |                 |
|    rating    | integer |    Bewertung    |

```php
array|boolean KODIADDONS_GetAddons(integer $InstanzeID);
```
 Liest die Eigenschaften aller Addons aus.
 Rückgabewert ist ein assoziiertes Array mit den Daten. Tritt ein Fehler auf, wird FALSE zurückgegeben.
 Es gilt die Tabelle von KODIADDONS_GetAddonDetails.

```php
boolean KODIADDONS_SetAddonEnabled(integer $InstanzeID, boolean $Value);
```
 De-/Aktiviert ein Addon.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Addons können direkt über die HTML-Tabelle ausgeführt werden
- Enable/Disable über Checkboxen in der Tabelle

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
