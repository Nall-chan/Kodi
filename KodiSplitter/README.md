[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#7-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#6-anhang)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#6-anhang)

# Kodi Splitter <!-- omit in toc -->

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

- Zentrale Kommunikationsinstanz für die JSON-RPC-Verbindung zu Kodi
- Verwaltung der TCP-Verbindung zum Kodi-Server
- Verteilt Events an die Device-Instanzen
- Implementiert KeepAlive und Watchdog-Funktionalität

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Kodi-Installation mit aktiviertem JSON-RPC Interface

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

Die Instanz wird automatisch vom KodiConfigurator angelegt. Manuelle Konfiguration:

1. Instanz anlegen (Typ: KodiSplitter)
2. Host und Port des Kodi-Servers eintragen
3. Bei Bedarf Authentifizierung aktivieren

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| Open | boolean | false | Verbindung herstellen |
| Host | string | | Hostnamen, IP-Adresse von Kodi |
| Port | integer | 9090 | Ziel-Port in Kodi für die RPC-JSON-API via TCP |
| Webport | integer | 80 | Webfront von Kodi, wird für den download der Cover genutzt |
| Watchdog | boolean | false | Mit Ping prüfen bevor versucht wird eine Verbindung aufzubauen |
| Interval | integer | 5 | Interval der Ping-Prüfung |

## 5. Statusvariablen

Keine Statusvariablen - dient nur als Kommunikationsschnittstelle

## 6. Visualisierung

![Splitter](imgs/Splitter.png)

## 7. PHP-Befehlsreferenz

//Todo
1-8 vorbereitet
9 ff fehlt

## 8. Aktionen

Keine direkten Aktionen verfügbar

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
