[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Discovery <!-- omit in toc -->  

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

- Automatische Erkennung von Kodi-Geräten im lokalen Netzwerk per mDNS
- Anzeige der gefundenen Geräte in einer Übersicht
- Startpunkt für die Konfiguration neuer Kodi-Instanzen

## 2. Voraussetzungen

- Symcon ab Version 9.0
- Kodi-Geräte müssen im selben Netzwerk erreichbar sein
- Multicast muss im Netzwerk erlaubt sein (kein Docker mit NAT)

## 3. Software-Installation

Die Installation erfolgt über den Module Store von IP-Symcon.

## 4. Einrichten der Instanzen in IP-Symcon

1. Im Objektbaum die Kategorie 'Discovery Instanzen' öffnen
2. Auf die Instanz 'Kodi Discovery' doppelt klicken
3. Gefundene Geräte werden automatisch angezeigt
4. Mit "Erstellen" und dann "Konfigurieren" die Instanzen anlegen

![Discovery](imgs/Discovery.png)
![Discovery Dialog](imgs/Discovery1.png)

## 5. Statusvariablen

Keine Statusvariablen - dient nur zur Geräteerkennung

## 6. Visualisierung

- Zeigt gefundene Geräte in einer Tabelle an
- Buttons für Erstellen und Konfigurieren

## 7. PHP-Befehlsreferenz

Keine direkten PHP-Befehle - wird über die Benutzeroberfläche bedient

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
