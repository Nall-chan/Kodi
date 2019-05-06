<?php

declare(strict_types=1);

/*
 * @addtogroup kodi
 * @{
 *
 * @package       Kodi
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.0
 *
 */
require_once(__DIR__ . '/../libs/KodiClass.php');  // diverse Klassen

/**
 * KodiDeviceVideoLibrary Klasse für den Namespace VideoLibrary der KODI-API.
 * Erweitert KodiBase.
 *
 * @package       Kodi
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 * @todo Suche über WF einbauen. String und Int-Var für Text suche in Titel/Genre etc... Ergebnis als HTML-Tabelle.
 * @todo VideoLibrary.GetInProgressTVShows ab v8
 * @todo VideoLibrary.GetTags ab v8
 */
class KodiDeviceVideoLibrary extends KodiBase
{
    /**
     * RPC-Namespace
     *
     * @access private
     *  @var string
     * @value 'VideoLibrary'
     */
    protected static $Namespace = 'VideoLibrary';

    /**
     * Alle Properties des RPC-Namespace
     *
     * @access private
     *  @var array
     */
    protected static $Properties = [];

    /**
     * Alle Eigenschaften von Episoden.
     *
     * @access private
     *  @var array
     */
    protected static $EpisodeItemList = [
        'title',
        'plot',
        'votes',
        'rating',
        'writer',
        'firstaired',
        'playcount',
        'runtime',
        'director',
        'productioncode',
        'season',
        'episode',
        'originaltitle',
        'showtitle',
        'cast',
        'streamdetails',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'resume',
        'tvshowid',
        'dateadded',
        'uniqueid',
        'art'
    ];

    /**
     * Ein Teil der Eigenschaften von Episoden.
     *
     * @access private
     *  @var array
     */
    protected static $EpisodeItemListSmall = [
        'title',
        'playcount',
        'season',
        'episode',
        'originaltitle',
        'showtitle',
        'fanart',
        'thumbnail',
        'file',
        'tvshowid'
    ];

    /**
     * Alle Eigenschaften von Filmen.
     *
     * @access private
     *  @var array
     */
    protected static $MovieItemList = [
        'title',
        'genre',
        'year',
        'rating',
        'director',
        'trailer',
        'tagline',
        'plot',
        'plotoutline',
        'originaltitle',
        'lastplayed',
        'playcount',
        'writer',
        'studio',
        'mpaa',
        'cast',
        'country',
        'imdbnumber',
        'runtime',
        'set',
        'showlink',
        'streamdetails',
        'top250',
        'votes',
        'fanart',
        'thumbnail',
        'file',
        'sorttitle',
        'resume',
        'setid',
        'dateadded',
        'tag',
        'art'];

    /**
     * Alle Eigenschaften von Filmsets.
     *
     * @access private
     *  @var array
     */
    protected static $SetItemList = [
        'title',
        'playcount',
        'fanart',
        'thumbnail',
        'art'
    ];

    /**
     * Alle Eigenschaften von Seasons.
     *
     * @access private
     *  @var array
     */
    protected static $SeasonItemList = [
        'season',
        'showtitle',
        'playcount',
        'episode',
        'fanart',
        'thumbnail',
        'tvshowid',
        'watchedepisodes',
        'art'
    ];

    /**
     * Alle Eigenschaften von Musikvideos.
     *
     * @access private
     *  @var array
     */
    protected static $MusicVideoItemList = [
        'title',
        'playcount',
        'runtime',
        'director',
        'studio',
        'year',
        'plot',
        'album',
        'artist',
        'genre',
        'track',
        'streamdetails',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'resume',
        'dateadded',
        'tag',
        'art'
    ];

    /**
     * Alle Eigenschaften von TV-Serien.
     *
     * @access private
     *  @var array
     */
    protected static $TvShowItemList = [
        'title',
        'genre',
        'year',
        'rating',
        'plot',
        'studio',
        'mpaa',
        'cast',
        'playcount',
        'episode',
        'imdbnumber',
        'premiered',
        'votes',
        'lastplayed',
        'fanart',
        'thumbnail',
        'file',
        'originaltitle',
        'sorttitle',
        'episodeguide',
        'season',
        'watchedepisodes',
        'dateadded',
        'tag',
        'art'
    ];

    /**
     * Alle Eigenschaften von Genres.
     *
     * @access private
     *  @var array
     */
    protected static $GenreItemList = [
        'thumbnail',
        'title'
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyBoolean('showDoScan', true);
        $this->RegisterPropertyBoolean('showDoClean', true);
        $this->RegisterPropertyBoolean('showScan', true);
        $this->RegisterPropertyBoolean('showClean', true);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        $this->RegisterProfileIntegerEx('Action.Kodi', '', '', '', [
            [0, 'Ausführen', '', -1]
        ]);

        if ($this->ReadPropertyBoolean('showDoScan')) {
            $this->RegisterVariableInteger('doscan', 'Suche nach neuen / veränderten Inhalten', 'Action.Kodi', 1);
            $this->EnableAction('doscan');
        } else {
            $this->UnregisterVariable('doscan');
        }

        if ($this->ReadPropertyBoolean('showScan')) {
            $this->RegisterVariableBoolean('scan', 'Datenbanksuche läuft', '~Switch', 2);
        } else {
            $this->UnregisterVariable('scan');
        }

        if ($this->ReadPropertyBoolean('showDoClean')) {
            $this->RegisterVariableInteger('doclean', 'Bereinigen der Datenbank', 'Action.Kodi', 3);
            $this->EnableAction('doclean');
        } else {
            $this->UnregisterVariable('doclean');
        }

        if ($this->ReadPropertyBoolean('showClean')) {
            $this->RegisterVariableBoolean('clean', 'Bereinigung der Datenbank läuft', '~Switch', 4);
        } else {
            $this->UnregisterVariable('clean');
        }

        parent::ApplyChanges();
    }

    ################## PRIVATE
    protected function Decode($Method, $KodiPayload)
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

    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction($Ident, $Value)
    {
        if (parent::RequestAction($Ident, $Value)) {
            return true;
        }
        switch ($Ident) {
            case 'doclean':
                return $this->Clean();
            case 'doscan':
                return $this->Scan();
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIVIDOLIB_Clean'. Startet das bereinigen der Datenbank
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Clean()
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
        trigger_error('Error start cleaning', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDOLIB_Export'. Exportiert die Video Datenbank.
     *
     * @access public
     * @param  string $Path Ziel-Verzeichnis für den Export.
     * @param bool $Overwrite Vorhandene Daten überschreiben.
     * @param bool $includeImages Bilder mit exportieren.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Export(string $Path, bool $Overwrite, bool $includeImages)
    {
        if (!is_string($Path) or ( strlen($Path) < 2)) {
            trigger_error('Path is invalid', E_USER_NOTICE);
            return false;
        }
        if (!is_bool($Overwrite)) {
            trigger_error('Overwrite must be boolean', E_USER_NOTICE);
            return false;
        }
        if (!is_bool($includeImages)) {
            trigger_error('includeImages must be boolean', E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Export(['options' => ['path' => $Path, 'overwrite' => $Overwrite, 'images' => $includeImages]]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return ($ret === 'OK');
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetEpisodeDetails'. Liest die Eigenschaften eines Künstlers aus.
     *
     * @access public
     * @param  int $EpisodeId EpisodenID der zu lesenden Episode.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetEpisodeDetails(int $EpisodeId)
    {
        if (!is_int($EpisodeId)) {
            trigger_error('EpisodeId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetEpisodeDetails(['episodeid' => $EpisodeId, 'properties' => static::$EpisodeItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->episodedetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetEpisodes'. Liest die Eigenschaften aller Songs aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetEpisodes()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetEpisodes(['properties' => static::$EpisodeItemListSmall]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->episodes);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetGenres'. Liest die Eigenschaften aller Genres aus.
     *
     * @access public
     * @param string $Type Der Typ der zu suchenden Genres.
     *   enum["movie"=Filme, "tvshow"=Serien, "musicvideo"=Musikvideos]
     *    * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetGenres(string $Type)
    {
        $Type = strtolower($Type);
        if (!in_array($Type, ['movie', 'musicvideo', 'tvshow'])) {
            trigger_error('Media must be "movie", "tvshow", or "musicvideo".', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetGenres(['properties' => static::$GenreItemList, 'type' => $Type]);
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
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieDetails'. Liest die Eigenschaften eines Films aus.
     *
     * @access public
     * @param  int $MovieId MovieID des zu lesenden Films.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieDetails(int $MovieId)
    {
        if (!is_int($MovieId)) {
            trigger_error('MovieId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetEpisodeDetails(['movieid' => $MovieId, 'properties' => static::$MovieItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->moviedetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovies'. Liest die Eigenschaften aller Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovies()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovies(['properties' => static::$MovieItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->movies);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieSetDetails'. Liest die Eigenschaften eines Film-Sets aus.
     *
     * @access public
     * @param  int $SetId SetId des zu lesenden Film-Sets.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieSetDetails(int $SetId)
    {
        if (!is_int($SetId)) {
            trigger_error('SetId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovieSetDetails(['setid' => $SetId, 'properties' => static::SetItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->setdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMovieSets'. Liest die Eigenschaften aller Film-Sets.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMovieSets()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMovieSets(['properties' => static::$SetItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->sets);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMusicVideoDetails'. Liest die Eigenschaften eines Musikvideos aus.
     *
     * @access public
     * @param  int $MusicVideoId MusicVideoId des zu lesenden Musikvideos.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMusicVideoDetails(int $MusicVideoId)
    {
        if (!is_int($MusicVideoId)) {
            trigger_error('MusicVideoId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMusicVideoDetails(['musicvideoid' => $MusicVideoId, 'properties' => static::$MusicVideoItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->musicvideodetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetMusicVideos'. Liest die Eigenschaften aller Musikvideos.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetMusicVideos()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetMusicVideos(['properties' => static::$MusicVideoItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->musicvideos);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedEpisodes'. Liest die Eigenschaften der zuletzt hinzugefügten Episoden aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedEpisodes()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedEpisodes(['properties' => static::$EpisodeItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->episodes);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedMovies'. Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedMovies()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedMovies(['properties' => static::$MovieItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->movies);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetRecentlyAddedMusicVideos'. Liest die Eigenschaften der zuletzt hinzugefügten Filme aus.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetRecentlyAddedMusicVideos()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetRecentlyAddedMusicVideos(['properties' => static::$MusicVideoItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->musicvideos);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetSeasons'. Liest die Eigenschaften eines Musikvideos aus.
     *
     * @access public
     * @param  int $TvShowId TvShowId der zu lesenden Serie.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetSeasons(int $TvShowId)
    {
        if (!is_int($TvShowId)) {
            trigger_error('TvShowId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetSeasons(['tvshowid' => $TvShowId, 'properties' => static::$SeasonItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->seasons);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTVShowDetails'. Liest die Eigenschaften eines TV-Serie aus.
     *
     * @access public
     * @param  int $TvShowId TvShowId der zu lesenden TV-Serie.
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetTVShowDetails(int $TvShowId)
    {
        if (!is_int($TvShowId)) {
            trigger_error('TvShowId must be integer', E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTVShowDetails(['tvshowid' => $TvShowId, 'properties' => static::$TvShowItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->tvshowdetails);
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_GetTVShows'. Liest die Eigenschaften aller TV-Serien.
     *
     * @access public
     * @return array | bool Array mit den Daten oder false bei Fehlern.
     */
    public function GetTVShows()
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetTVShows(['properties' => static::$TvShowItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->limits->total > 0) {
            return $KodiData->ToArray($ret->tvshows);
        }
        return [];
    }

    /**
     * IPS-Instanz-Funktion 'KODIVIDEOLIB_Scan'. Startet das Scannen der Quellen für neue Einträge in der Datenbank.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function Scan()
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
        trigger_error('Error start scanning', E_USER_NOTICE);
        return false;
    }

}

/** @} */
