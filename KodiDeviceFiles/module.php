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
 * @version       2.95
 *
 */
require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDeviceFiles Klasse für den Namespace Files der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2020 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.95
 * @example <b>Ohne</b>
 * @todo Suche über WF einbauen. String und Int-Var für Text suche in Album/Artist etc... Ergebnis als HTML-Tabelle.
 */
class KodiDeviceFiles extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     *  @var string
     * @value 'Files'
     */
    protected static $Namespace = 'Files';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [];

    /**
     * Alle Properties eines Item
     *
     * @access private
     *  @var array
     */
    protected static $ItemListFull = [
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
        'director',
        'trailer',
        'tagline',
        'plot',
        'plotoutline',
        'originaltitle',
        'lastplayed',
        'writer',
        'studio',
        'mpaa',
        'cast',
        'country',
        'imdbnumber',
        'premiered',
        'productioncode',
        'runtime',
        'set',
        'showlink',
        'streamdetails',
        'top250',
        'votes',
        'firstaired',
        'season',
        'episode',
        'showtitle',
        'thumbnail',
        'file',
        'resume',
        'artistid',
        'albumid',
        'tvshowid',
        'setid',
        'watchedepisodes',
        'disc',
        'tag',
        'art',
        'genreid',
        'displayartist',
        'albumartistid',
        'description',
        'theme',
        'mood',
        'style',
        'albumlabel',
        'sorttitle',
        'episodeguide',
        'uniqueid',
        'dateadded',
        'size',
        'lastmodified',
        'mimetype'];

    /**
     * Kleiner Teil der Properties eines Item
     *
     * @access private
     *  @var array
     */
    protected static $ItemListSmall = [
        'title',
        'artist',
        'albumartist',
        'genre',
        'year',
        'album',
        'track',
        'duration',
        'plot',
        'runtime',
        'season',
        'episode',
        'showtitle',
        'thumbnail',
        'file',
        'disc',
        'albumlabel',
    ];

    ################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'KODIFILES_GetSources'. Liefert alle bekannten Quellen nach Typ.
     *
     * @access public
     * @param string $Media Der Typ der zu suchenden Quellen.
     *   enum["video"=Video, "music"=Musik, "pictures"=Bilder, "files"=Dateien, "programs"=Programme]
     * @return array|bool Array mit den Quellen oder false bei Fehler.
     */
    public function GetSources(string $Media)
    {
        if (!is_string($Media)) {
            trigger_error('Media must be string', E_USER_NOTICE);
            return false;
        }

        $Media = strtolower($Media);
        if (!in_array($Media, ['video', 'music', 'pictures', 'files', 'programs'])) {
            trigger_error('Media must be "video", "music", "pictures", "files" or "programs".', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSources(['media' => $Media]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->sources);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIFILES_GetFileDetails'. Liefert alle Details einer Datei.
     *
     * @access public
     * @param string $File Dateipfad und Name der gesuchten Datei.
     * @param string $Media Der Typ der Datei gibt die zu suchenden Eigenschaften an.
     *   enum["video"=Video, "music"=Musik, "pictures"=Bilder, "files"=Dateien, "programs"=Programme]
     * @return array|bool Array mit den Quellen oder false bei Fehler.
     */
    public function GetFileDetails(string $File, string $Media)
    {
        if (!is_string($File)) {
            trigger_error('File must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Media)) {
            trigger_error('Media must be string', E_USER_NOTICE);
            return false;
        }

        $Media = strtolower($Media);
        if (!in_array($Media, ['video', 'music', 'pictures', 'files', 'programs'])) {
            trigger_error('Media must be "video", "music", "pictures", "files" or "programs".', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetFileDetails(['file' => $File, 'media' => $Media, 'properties' => static::$ItemListFull]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->filedetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIFILES_GetDirectory'. Liefert Informationen zu einem Verzeichnis.
     *
     * @access public
     * @param string $Directory Verzeichnis welches durchsucht werden soll.
     * @return array|bool Array mit den Quellen oder false bei Fehler.
     */
    public function GetDirectory(string $Directory)
    {
        if (!is_string($Directory)) {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace); // 'GetDirectory', ['directory' => $Directory]);
        $KodiData->GetDirectory(['directory' => $Directory]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }

        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->files);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIFILES_GetDirectoryDetails'. Liefert alle Details eines Verzeichnisses.
     *
     * @access public
     * @param string $Directory Verzeichnis welches durchsucht werden soll.
     * @param string $Media Der Typ der Datei gibt die zu liefernden Eigenschaften an.
     *   enum["video"=Video, "music"=Musik, "pictures"=Bilder, "files"=Dateien, "programs"=Programme]
     * @return array|bool Array mit den Quellen oder false bei Fehler.
     */
    public function GetDirectoryDetails(string $Directory, string $Media)
    {
        if (!is_string($Directory)) {
            trigger_error('Directory must be string', E_USER_NOTICE);
            return false;
        }
        if (!is_string($Media)) {
            trigger_error('Media must be string', E_USER_NOTICE);
            return false;
        }

        $Media = strtolower($Media);
        if (!in_array($Media, ['video', 'music', 'pictures', 'files', 'programs'])) {
            trigger_error('Media must be "video", "music", "pictures", "files" or "programs".', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace); //, 'GetDirectory', ['directory' => $Directory, 'media' => $Media, 'properties' => static::$ItemListSmall]);
        $KodiData->GetDirectory(['directory' => $Directory, 'media' => $Media, 'properties' => static::$ItemListSmall]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }

        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->files);
        }
        return [];
    }

    ################## PRIVATE

    /**
     * Keine Funktion.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $KodiPayload)
    {
        return;
    }
}

/** @} */
