[![SDK](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
[![Module Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.version&label=Modul%20Version&color=blue)](https://community.symcon.de/t/modul-kodi/41646)
[![Symcon Version](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FNall-chan%2FKodi%2Frefs%2Fheads%2Fmaster%2Flibrary.json&query=%24.compatibility.version&suffix=%3E&label=Symcon%20Version&color=green)](https://www.symcon.de/de/service/dokumentation/installation/migrationen/v80-v81-q3-2025/)
[![License](https://img.shields.io/badge/License-Custom--NC--SA-green.svg)](#12-lizenz)
[![Check Style](https://github.com/Nall-chan/Kodi/workflows/Check%20Style/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![Run Tests](https://github.com/Nall-chan/Kodi/workflows/Run%20Tests/badge.svg)](https://github.com/Nall-chan/Kodi/actions)
[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](#4-spenden)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](#4-spenden)

# Kodi Input <!-- omit in toc -->

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

- Senden von Tastendrücken an Kodi
- Texteingabe an Kodi senden
- Anzeige eines virtuellen Remotes
- Erkennung von erforderlichen Eingaben

## 2. Voraussetzungen

- Funktionierender KodiSplitter
- Konfigurierte Verbindung zu einem Kodi-Gerät

## 3. Software-Installation

Dieses Modul ist Bestandteil der [Kodi-Library](../README.md#3-software-installation).

## 4. Einrichten der Instanzen in IP-Symcon

1. Wird normalerweise automatisch vom KodiConfigurator angelegt
2. Manuell anlegen über "Instanz hinzufügen" (Typ: KodiDeviceInput)
3. Mit übergeordnetem KodiSplitter verbinden
4. Remote-Typ auswählen (vertikal/horizontal)

**Konfigurationsseite:**

| Eigenschaft | Typ | Standardwert | Funktion |
| :---: | :---: | :---: | :--- |
| showSVGRemote | boolean | true | SVG-Remote anzeigen |
| RemoteId | integer | 1 | 1=vertikal, 2=horizontal |
| showNavigationButtons | boolean | true | Aktions-Variable zum navigieren anzeigen |
| showControlButtons | boolean | true | Aktions-Variable zum steuern anzeigen |
| showInputRequested | boolean | true | Status-Variable wenn Eingaben nötig sind |

## 5. Statusvariablen

- InputRequested (zeigt an, wenn Kodi eine Eingabe erwartet)

## 6. Visualisierung

![Input](imgs/Input.png)
![Input WebFront](imgs/Input_WF.png)

## 7. PHP-Befehlsreferenz

```php
boolean KODIINPUT_Up(integer $InstanzeID);
```
 Sendet den Tastendruck 'hoch'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Down(integer $InstanzeID);
```
 Sendet den Tastendruck 'runter'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Left(integer $InstanzeID);
```
 Sendet den Tastendruck 'links'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Right(integer $InstanzeID);
```
 Sendet den Tastendruck 'rechts'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_ContextMenu(integer $InstanzeID);
```
 Sendet den Tastendruck 'Context-Menü'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Home(integer $InstanzeID);
```
 Sendet den Tastendruck 'Home'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Info(integer $InstanzeID);
```
 Sendet den Tastendruck 'Info'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_Select(integer $InstanzeID);
```
 Sendet den Tastendruck 'auswählen'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_ShowOSD(integer $InstanzeID);
```
 Sendet den Tastendruck 'OSD anzeigen'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_ShowCodec(integer $InstanzeID);
```
 Sendet den Tastendruck 'Codec anzeigen'.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

```php
boolean KODIINPUT_ExecuteAction(integer $InstanzeID, string $Action);
```
 Sendet die in $Action übergebene Aktion.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.
 $Action kann sein:
 "left", "right", "up", "down", "pageup", "pagedown", "select", "highlight", "parentdir",
 "parentfolder", "back", "previousmenu", "info", "pause", "stop", "skipnext", "skipprevious",
 "fullscreen", "aspectratio", "stepforward", "stepback", "bigstepforward", "bigstepback",
 "chapterorbigstepforward", "chapterorbigstepback", "osd", "showsubtitles", "nextsubtitle",
 "cyclesubtitle", "codecinfo", "nextpicture", "previouspicture", "zoomout", "zoomin",
 "playlist", "queue", "zoomnormal", "zoomlevel1", "zoomlevel2", "zoomlevel3", "zoomlevel4",
 "zoomlevel5", "zoomlevel6", "zoomlevel7", "zoomlevel8", "zoomlevel9", "nextcalibration",
 "resetcalibration", "analogmove", "analogmovex", "analogmovey", "rotate", "rotateccw",
 "close", "subtitledelayminus", "subtitledelay", "subtitledelayplus", "audiodelayminus",
 "audiodelay", "audiodelayplus", "subtitleshiftup", "subtitleshiftdown", "subtitlealign",
 "audionextlanguage", "verticalshiftup", "verticalshiftdown", "nextresolution", "audiotoggledigital",
 "number0", "number1", "number2", "number3", "number4", "number5", "number6", "number7",
 "number8", "number9", "osdleft", "osdright", "osdup", "osddown", "osdselect", "osdvalueplus",
 "osdvalueminus", "smallstepback", "fastforward", "rewind", "play", "playpause", "switchplayer",
 "delete", "copy", "move", "mplayerosd", "hidesubmenu", "screenshot", "rename", "togglewatched",
 "scanitem", "reloadkeymaps", "volumeup", "volumedown", "mute", "backspace", "scrollup",
 "scrolldown", "analogfastforward", "analogrewind", "moveitemup", "moveitemdown", "contextmenu",
 "shift", "symbols", "cursorleft", "cursorright", "showtime", "analogseekforward", "analogseekback",
 "showpreset", "nextpreset", "previouspreset", "lockpreset", "randompreset","increasevisrating",
 "decreasevisrating", "showvideomenu", "enter", "increaserating", "decreaserating", "togglefullscreen",
 "nextscene", "previousscene", "nextletter", "prevletter", "jumpsms2", "jumpsms3", "jumpsms4",
 "jumpsms5", "jumpsms6", "jumpsms7", "jumpsms8", "jumpsms9", "filter", "filterclear","filtersms2",
 "filtersms3", "filtersms4", "filtersms5", "filtersms6", "filtersms7", "filtersms8","filtersms9",
 "firstpage", "lastpage", "guiprofile", "red", "green", "yellow", "blue", "increasepar",
 "decreasepar", "volampup", "volampdown", "volumeamplification", "createbookmark","createepisodebookmark",
 "settingsreset", "settingslevelchange", "stereomode", "nextstereomode","previousstereomode",
 "togglestereomode", "stereomodetomono", "channelup", "channeldown","previouschannelgroup",
 "nextchannelgroup", "playpvr", "playpvrtv", "playpvrradio", "record", "leftclick", "rightclick",
 "middleclick", "doubleclick", "longclick", "wheelup", "wheeldown", "mousedrag", "mousemove",
 "tap", "longpress", "pangesture", "zoomgesture", "rotategesture", "swipeleft", "swiperight",
 "swipeup", "swipedown", "error", "noop"

```php
boolean KODIINPUT_SendText(integer $InstanzeID, string $Text, boolean $Done);
```
 Sendet den in $Text rn Text an Kodi.
 Mit $Done = true kann die Eingabe beendet werden.
 Rückgabewert TRUE bei erfolgreicher Ausführung, sonst FALSE.

## 8. Aktionen

- Navigation über virtuelle Remote
- Texteingabe über Dialog
- Direkte Ausführung von Aktionen

## 9. Anhang

### Changelog

Siehe Haupt-[README](../README.md)

### Spenden

[![PayPal.Me](https://img.shields.io/badge/PayPal-Me-lightblue.svg)](https://paypal.me/Nall4chan)
[![Wunschliste](https://img.shields.io/badge/Wunschliste-Amazon-ff69fb.svg)](https://www.amazon.de/hz/wishlist/ls/YU4AI9AQT9F?ref_=wl_share)

## 10. Lizenz

[CC BY-NC-SA 4.0](../LICENSE)
