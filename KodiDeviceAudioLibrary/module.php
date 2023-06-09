<?php

declare(strict_types=1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceAudioLibrary Klasse für den Namespace AudioLibrary der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       3.00
 * @example <b>Ohne</b>
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $AlbumItemList Alle Eigenschaften von Alben
 * @property array $AlbumItemListSmall  Ein Teil der Eigenschaften der Alben
 * @property array $ArtistItemList Alle Eigenschaften von Künstlern
 * @property array $GenreItemList  Alle Eigenschaften von Genres
 * @property array $SongItemList  Alle Eigenschaften von Songs
 * @todo Suche über WF einbauen. String und Int-Var für Text suche in Album/Artist etc... Ergebnis als HTML-Tabelle.
 * @todo AudioLibrary.GetProperties ab v8
 * @todo AudioLibrary.GetAvailableArt ab V10
 * @todo AudioLibrary.GetAvailableArtTypes ab V10
 */
class KodiDeviceAudioLibrary extends KodiBase
{
    public const PropertyShowDoScan = 'showDoScan';
    public const PropertyShowDoClean = 'showDoClean';
    public const PropertyShowScan = 'showScan';
    public const PropertyShowClean = 'showClean';

    protected static $Namespace = 'AudioLibrary';
    protected static $Properties = [];
    protected static $AlbumItemList = [
        'theme',
        'description',
        'type',
        'style',
        'playcount',
        'albumlabel',
        'mood',
        'displayartist',
        'artist',
        'genreid',
        'musicbrainzalbumartistid',
        'year',
        'rating',
        'artistid',
        'title',
        'musicbrainzalbumid',
        'genre',
        'fanart',
        'thumbnail'
    ];
    protected static $AlbumItemListSmall = [
        'playcount',
        'albumlabel',
        'displayartist',
        'year',
        'rating',
        'title',
        'fanart',
        'thumbnail'
    ];
    protected static $ArtistItemList = [
        'born',
        'formed',
        'died',
        'style',
        'yearsactive',
        'mood',
        'musicbrainzartistid',
        'disbanded',
        'description',
        'instrument',
        'genre',
        'fanart',
        'thumbnail'
    ];
    protected static $GenreItemList = [
        'thumbnail',
        'title'
    ];
    protected static $SongItemList = [
        'title',
        'artist',
        'albumartist',
        'genre',
        'year',
        'rating',
        'album',
        'track',
        'duration',
        'comment',
        'lyrics',
        'musicbrainztrackid',
        'musicbrainzartistid',
        'musicbrainzalbumid',
        'musicbrainzalbumartistid',
        'playcount',
        'fanart',
        'thumbnail',
        'file',
        'albumid',
        'lastplayed',
        'disc',
        'genreid',
        'artistid',
        'displayartist',
        'albumartistid'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyBoolean(self::PropertyShowDoScan, true);
        $this->RegisterPropertyBoolean(self::PropertyShowDoClean, true);
        $this->RegisterPropertyBoolean(self::PropertyShowScan, true);
        $this->RegisterPropertyBoolean(self::PropertyShowClean, true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, $this->Translate('Execute'), '', -1]
        ]);

        if ($this->ReadPropertyBoolean(self::PropertyShowDoScan)) {
            $this->RegisterVariableInteger('doscan', $this->Translate('Search for new / changed content'), 'Action.Kodi', 1);
            $this->EnableAction('doscan');
        } else {
            $this->UnregisterVariable('doscan');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowScan)) {
            $this->RegisterVariableBoolean('scan', $this->Translate('Database search in progress'), '~Switch', 2);
        } else {
            $this->UnregisterVariable('scan');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowDoClean)) {
            $this->RegisterVariableInteger('doclean', $this->Translate('Clean up the database'), 'Action.Kodi', 3);
            $this->EnableAction('doclean');
        } else {
            $this->UnregisterVariable('doclean');
        }

        if ($this->ReadPropertyBoolean(self::PropertyShowClean)) {
            $this->RegisterVariableBoolean('clean', $this->Translate('Database cleanup in progress'), '~Switch', 4);
        } else {
            $this->UnregisterVariable('clean');
        }

        parent::ApplyChanges();
    }

    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction(string $Ident, mixed $Value, bool &$done = false): void
    {
        parent::RequestAction($Ident, $Value, $done);
        if ($done) {
            return;
        }
        switch ($Ident) {
            case 'doclean':
                $this->Clean();
                return;
            case 'doscan':
                $this->Scan();
                return;
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_Clean'. Startet das bereinigen der Datenbank
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Clean(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Clean();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start cleaning'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_Export'. Exportiert die Audio Datenbank.
     *
     * @access public
     * @param  string $Path Ziel-Verzeichnis für den Export.
     * @param bool $Overwrite Vorhandene Daten überschreiben.
     * @param bool $includeImages Bilder mit exportieren.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Export(string $Path, bool $Overwrite, bool $includeImages): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Export(['options' => ['path' => $Path, 'overwrite' => $Overwrite, 'images' => $includeImages]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $ret === 'OK';
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetAlbumDetails'. Liest die Eigenschaften eines Album aus.
     *
     * @access public
     * @param  int $AlbumID AlbumID des zu lesenden Alben.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetAlbumDetails(int $AlbumID): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAlbumDetails(['albumid' => $AlbumID, 'properties' => static::$AlbumItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->albumdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetAlbums'. Liest die Eigenschaften aller Alben aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetAlbums(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAlbums(['properties' => static::$AlbumItemListSmall]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->albums);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetArtistDetails'. Liest die Eigenschaften eines Künstlers aus.
     *
     * @access public
     * @param  int $ArtistID ArtistID des zu lesenden Künstlers.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetArtistDetails(int $ArtistID): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetArtistDetails(['artistid' => $ArtistID, 'properties' => static::$ArtistItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->artistdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetArtists'. Liest die Eigenschaften aller Künstler aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetArtists(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetArtists(['properties' => static::$ArtistItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->artists);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetGenres'. Liest die Eigenschaften aller Genres aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetGenres(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetGenres(['properties' => static::$GenreItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->genres);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetRecentlyAddedAlbums'. Liest die Eigenschaften der zuletzt hinzugefügten Alben aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedAlbums(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedAlbums(['properties' => static::$AlbumItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->albums);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetRecentlyAddedSongs'. Liest die Eigenschaften der zuletzt hinzugefügten Songs aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedSongs(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedSongs(['properties' => static::$SongItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->songs);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetRecentlyPlayedAlbums'. Liest die Eigenschaften der zuletzt abgespielten Alben aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyPlayedAlbums(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyPlayedAlbums(['properties' => static::$AlbumItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->albums);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetRecentlyPlayedSongs'. Liest die Eigenschaften der zuletzt abgespielten Songs aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyPlayedSongs(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyPlayedSongs(['properties' => static::$SongItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->songs);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetSongDetails'. Liest die Eigenschaften eines Künstlers aus.
     *
     * @access public
     * @param  int $SongID SongID des zu lesenden Songs.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetSongDetails(int $SongID): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSongDetails(['songid' => $SongID, 'properties' => static::$SongItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->songdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_GetSongs'. Liest die Eigenschaften aller Songs aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetSongs(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSongs(['properties' => static::$SongItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->songs);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIAUDIOLIB_Scan'. Startet das Scannen der Quellen für neue Einträge in der Datenbank.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Scan(): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Scan();
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error start scanning'), E_USER_NOTICE);
        return false;
    }

    ################## PRIVATE
    /**
     * Dekodiert die empfangenen Events.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        switch ($Method) {
            case 'OnScanStarted':
                $this->SetValueBoolean('scan', true);
                break;
            case 'OnScanFinished':
                $this->SetValueBoolean('scan', false);
                break;
            case 'OnCleanStarted':
                $this->SetValueBoolean('clean', true);
                break;
            case 'OnCleanFinished':
                $this->SetValueBoolean('clean', false);
                break;
        }
    }
}

/** @} */
