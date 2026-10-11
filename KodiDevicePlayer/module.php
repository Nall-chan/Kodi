<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/KodiClass.php';  // diverse Klassen

/**
 * KodiDevicePlayer Klasse für den Namespace Player der KODI-API.
 * Erweitert KodiBase.
 *
 * @project       KodiDevicePlayer
 * @file          module.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.02
 * @example <b>Ohne</b>
 *
 * @property bool $isActive
 * @property int $PlayerId
 *
 *
 * @property string $Namespace RPC-Namespace
 * @property array $Properties Alle Properties des RPC-Namespace
 * @property array $PartialProperties Ein Teil der Properties des RPC-Namespace für Statusmeldungen
 * @property array $ItemList Alle Properties eines Item
 * @property array $ItemListSmall Kleiner Teil der Properties eines Item
 * @property array $Playertype Key ist der Medientyp, Value die PlayerID
 */
class KodiDevicePlayer extends KodiBase
{
    public const PropertyPlayerID = 'PlayerID';
    public const PropertyCoverSize = 'CoverSize';
    public const PropertyCoverTyp = 'CoverTyp';
    public const PropertyShowVideoStream = 'showVideoStream';
    public const PropertyShowAudioDelay = 'showAudioDelay';
    public const PropertyShowViewMode = 'showViewMode';
    public const PropertyShowTempo = 'showTempo';
    public const ProfileAudioDelay = 'Kodi.AudioDelay';
    public const ProfileViewMode = 'Kodi.ViewMode';
    public const ProfileTempo = 'Kodi.Tempo';

    /** Medientypen von LoadEx => [Feld im Item von Player.Open, Wert ist eine ID, Zusatzfelder]. */
    public const LoadTypes = [
        'Album'              => ['albumid', true, []],
        'Artist'             => ['artistid', true, []],
        'Genre'              => ['genreid', true, []],
        'Song'               => ['songid', true, []],
        'Movie'              => ['movieid', true, []],
        'Episode'            => ['episodeid', true, []],
        'Musicvideo'         => ['musicvideoid', true, []],
        'Channel'            => ['channelid', true, []],
        'Recording'          => ['recordingid', true, []],
        'File'               => ['file', false, []],
        'Directory'          => ['directory', false, []],
        'DirectoryRecursive' => ['directory', false, ['recursive' => true]],
        'Playlist'           => ['playlistid', true, []]
    ];

    /** Bildformate von Player.SetViewMode / Player.GetViewMode (Kodi 21), Index = Wert der Statusvariable. */
    public const ViewModes = [
        'normal',
        'zoom',
        'stretch4x3',
        'widezoom',
        'stretch16x9',
        'original',
        'stretch16x9nonlin',
        'zoom120width',
        'zoom110width'
    ];
    public const TimerName = 'PlayerStatus';
    public const ActionCreateTileLinks = 'CreateTileLinks';
    public const ActionRemoveTileLinks = 'RemoveTileLinks';
    public const GUIDApplication = '{3AF936C4-9B31-48EC-84D8-A30F0BEF104C}';
    public const GUIDPlaylist = '{7D73D0FF-0CC7-43D0-A196-0D6143E52756}';

    /**
     * Links für die Mediaplayer-Kachel.
     * Key ist der Ident des Links, Value die Quelle (Modul-GUID) und der Ident der Zielvariable.
     */
    public const TileLinks = [
        'LinkVolume'   => ['Module' => self::GUIDApplication, 'Ident' => 'volume', 'Position' => 4],
        'LinkMute'     => ['Module' => self::GUIDApplication, 'Ident' => 'mute', 'Position' => 5],
        'LinkPlaylist' => ['Module' => self::GUIDPlaylist, 'Ident' => 'CurrentPlaylist', 'Position' => 28]
    ];
    /**
     * PlayerID für Audio
     *
     * @access private
     * @static int
     * @value 0
     */
    public const Audio = 0;

    /**
     * PlayerID für Video
     *
     * @access private
     * @static int
     * @value 1
     */
    public const Video = 1;

    /**
     * PlayerID für Bilder
     *
     * @access private
     * @static int
     * @value 2
     */
    public const Picture = 2;

    /**
     * Idents früherer Versionen, für die es eine kompatible aktuelle Variable gibt.
     * Key ist der alte Ident, Value der neue Ident und der Variablentyp.
     */
    private const LegacyVariablesRename = [
        'totaltime'  => ['Ident' => 'duration', 'Type' => VARIABLETYPE_STRING],
        'shuffeld'   => ['Ident' => 'shuffled', 'Type' => VARIABLETYPE_BOOLEAN],
        '_speed'     => ['Ident' => 'speed', 'Type' => VARIABLETYPE_INTEGER],
        'audioindex' => ['Ident' => 'audiostream', 'Type' => VARIABLETYPE_INTEGER]
    ];

    /**
     * Idents früherer Versionen ohne aktuelle Entsprechung.
     */
    private const LegacyVariablesObsolete = [
        'type',
        'isactive',
        '_isactive',
        'audiocodec',
        'audiolanguage',
        'audiobitrate',
        'audiochannels',
        'audiostreams',
        'subtitles',
        'subtitleenabled'
    ];

    protected static $Namespace = 'Player';
    protected static $Properties = [
        'type',
        'partymode',
        'speed',
        'time',
        'percentage',
        'totaltime',
        'playlistid',
        'position',
        'repeat',
        'shuffled',
        'canseek',
        'canchangespeed',
        'canmove',
        'canzoom',
        'canrotate',
        'canshuffle',
        'canrepeat',
        'currentaudiostream',
        'audiostreams',
        'subtitleenabled',
        'currentsubtitle',
        'subtitles',
        'live',
        'currentvideostream',
        'videostreams'
    ];
    protected static $PartialProperties = [
        'type',
        'partymode',
        'speed',
        'time',
        'percentage',
        'repeat',
        'shuffled',
        'currentaudiostream',
        'subtitleenabled',
        'currentsubtitle',
        'currentvideostream'
    ];
    protected static $ItemList = [
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
        'channel',
        'channeltype',
        'hidden',
        'locked',
        'channelnumber',
        'starttime',
        'endtime'
    ];
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
    protected static $Playertype = [
        'unknown'   => -1,
        'song'      => 0,
        'audio'     => 0,
        'radio'     => 0,
        'video'     => 1,
        'episode'   => 1,
        'movie'     => 1,
        'tv'        => 1,
        'picture'   => 2,
        'pictures'  => 2
    ];

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyInteger(self::PropertyPlayerID, static::Audio);
        $this->PlayerId = static::Audio;
        $this->RegisterPropertyInteger(self::PropertyCoverSize, 300);
        $this->RegisterPropertyString(self::PropertyCoverTyp, 'thumb');
        $this->RegisterPropertyBoolean(self::PropertyShowVideoStream, true);
        $this->RegisterPropertyBoolean(self::PropertyShowAudioDelay, true);
        $this->RegisterPropertyBoolean(self::PropertyShowViewMode, true);
        $this->RegisterPropertyBoolean(self::PropertyShowTempo, true);
        $this->RegisterTimer(self::TimerName, 0, 'KODIPLAYER_RequestState($_IPS[\'TARGET\'],"PARTIAL");');
        $this->isActive = false;
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy(): void
    {
        if (IPS_GetKernelRunlevel() != KR_READY) {
            parent::Destroy();
            return;
        }
        if (!IPS_InstanceExists($this->InstanceID)) {
            $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
            if ($CoverID > 0) {
                @IPS_DeleteMedia($CoverID, true);
            }
            //Profile löschen
            $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
            $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');
            $this->UnregisterProfile('Speed.' . $this->InstanceID . '.Kodi');
            $this->UnregisterProfile($this->GetVideoStreamProfile());
        }
        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     * Wird beim Start von Symcon und nach einem Modul-Update ausgeführt.
     * Migriert Statusvariablen alter Versionen. Bestehende Instanzen erhalten die mit 3.03 neuen
     * Statusvariablen (Videospur, Audio-Verzögerung, Bildformat, Tempo) nicht ungefragt, die Eigenschaften
     * werden für sie auf false gesetzt. Neue Instanzen (ohne Migrate) legen sie an.
     *
     * @access public
     */
    public function Migrate(string $JSONData): string
    {
        if (IPS_GetKernelRunlevel() == KR_READY) {
            $this->MigrateLegacyVariables();
        }
        $Data = json_decode($JSONData);
        if (property_exists($Data->configuration, self::PropertyShowVideoStream)) {
            return '';
        }
        $Data->configuration->{self::PropertyShowVideoStream} = false;
        $Data->configuration->{self::PropertyShowAudioDelay} = false;
        $Data->configuration->{self::PropertyShowViewMode} = false;
        $Data->configuration->{self::PropertyShowTempo} = false;
        return json_encode($Data);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges(): void
    {
        $this->PlayerId = $this->ReadPropertyInteger(self::PropertyPlayerID);
        $this->UnregisterProfile('Repeat.Kodi');
        $this->UnregisterProfile('Status.Kodi');

        $this->RegisterProfileInteger('Intensity.Kodi', 'Intensity', '', ' %', 0, 100, 1);

        switch ($this->PlayerId) {
            case self::Audio:
                $this->UnregisterVariable('showtitle');
                $this->UnregisterVariable('season');
                $this->UnregisterVariable('episode');
                $this->UnregisterVariable('plot');
                $this->UnregisterVariable('audiostream');
                $this->UnregisterVariable('subtitle');
                $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
                $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');
                $this->UnregisterVideoVariables();

                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-32, '32 <<', '', -1],
                    [-16, '16 <<', '', -1],
                    [-8, '8 <<', '', -1],
                    [-4, '4 <<', '', -1],
                    [-2, '2 <<', '', -1],
                    [-1, '1 <<', '', -1],
                    [0, 'Pause', '', 0x0000FF],
                    [1, 'Play', '', 0x00FF00],
                    [2, '2 >>', '', -1],
                    [4, '4 >>', '', -1],
                    [8, '8 >>', '', -1],
                    [16, '16 >>', '', -1],
                    [32, '32 >>', '', -1]
                ]);

                $this->RegisterVariableString('album', 'Album', '', 15);
                $this->RegisterVariableInteger('track', 'Track', '', 16);
                $this->RegisterVariableInteger('disc', 'Disc', '', 17);
                $this->RegisterVariableString('artist', 'Artist', '~Artist', 20);
                $this->RegisterVariableString('lyrics', 'Lyrics', '', 30);
                $this->RegisterVariableBoolean('partymode', $this->Translate('Partymode'), '~Switch', 13);
                $this->EnableAction('partymode');
                $this->RegisterVariableInteger('repeat', $this->Translate('Repeat'), '~Repeat', 11);
                $this->EnableAction('repeat');
                $this->RegisterVariableInteger('year', $this->Translate('Year'), '', 19);
                $this->RegisterVariableString('genre', 'Genre', '', 21);
                $this->RegisterVariableString('duration', $this->Translate('Duration'), '', 24);
                $this->RegisterVariableString('time', $this->Translate('Runtime'), '', 25);
                break;
            case self::Video:
                $this->UnregisterVariable('album');
                $this->UnregisterVariable('track');
                $this->UnregisterVariable('disc');
                $this->UnregisterVariable('artist');
                $this->UnregisterVariable('lyrics');

                $this->RegisterProfileIntegerEx('AudioStream.' . $this->InstanceID . '.Kodi', '', '', '', [
                    [0, '1', '', -1]
                ]);
                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-32, '32 <<', '', -1],
                    [-16, '16 <<', '', -1],
                    [-8, '8 <<', '', -1],
                    [-4, '4 <<', '', -1],
                    [-2, '2 <<', '', -1],
                    [-1, '1 <<', '', -1],
                    [0, 'Pause', '', 0x0000FF],
                    [1, 'Play', '', 0x00FF00],
                    [2, '2 >>', '', -1],
                    [4, '4 >>', '', -1],
                    [8, '8 >>', '', -1],
                    [16, '16 >>', '', -1],
                    [32, '32 >>', '', -1]
                ]);

                $this->RegisterVariableString('showtitle', 'Serie', '', 13);
                $this->RegisterVariableString('channel', $this->Translate('Channel'), '', 13);
                $this->RegisterVariableInteger('season', $this->Translate('Season'), '', 15);
                $this->RegisterVariableInteger('episode', 'Episode', '', 16);

                $this->RegisterVariableString('plot', $this->Translate('Plot'), '~TextBox', 19);
                $this->RegisterVariableInteger('audiostream', $this->Translate('Audiostream'), 'AudioStream.' . $this->InstanceID . '.Kodi', 30);

                $this->RegisterProfileIntegerEx('Subtitels.' . $this->InstanceID . '.Kodi', '', '', '', [
                    [-1, $this->Translate('Off'), '', -1],
                    [0, 'Extern', '', -1]
                ]);
                $this->RegisterVariableInteger('subtitle', $this->Translate('Active subtitle'), 'Subtitels.' . $this->InstanceID . '.Kodi', 41);
                $this->RegisterVideoVariables();
                $this->RegisterVariableBoolean('partymode', $this->Translate('Partymode'), '~Switch', 13);
                $this->EnableAction('partymode');
                $this->RegisterVariableInteger('repeat', $this->Translate('Repeat'), '~Repeat', 11);
                $this->EnableAction('repeat');
                $this->RegisterVariableInteger('year', $this->Translate('Year'), '', 19);
                $this->RegisterVariableString('genre', 'Genre', '', 21);
                $this->RegisterVariableString('duration', 'Dauer', '', 24);
                $this->RegisterVariableString('time', $this->Translate('Runtime'), '', 25);
                break;

            case self::Picture:

                $this->UnregisterVariable('showtitle');
                $this->UnregisterVariable('season');
                $this->UnregisterVariable('episode');
                $this->UnregisterVariable('plot');
                $this->UnregisterVariable('audiostream');
                $this->UnregisterVariable('subtitle');
                $this->UnregisterProfile('AudioStream.' . $this->InstanceID . '.Kodi');
                $this->UnregisterProfile('Subtitels.' . $this->InstanceID . '.Kodi');

                $this->UnregisterVariable('album');
                $this->UnregisterVariable('track');
                $this->UnregisterVariable('disc');
                $this->UnregisterVariable('artist');
                $this->UnregisterVariable('lyrics');

                $this->UnregisterVariable('partymode');
                $this->UnregisterVariable('repeat');
                $this->UnregisterVariable('year');
                $this->UnregisterVariable('genre');
                $this->UnregisterVariable('duration');
                $this->UnregisterVariable('time');
                $this->UnregisterVideoVariables();
                $this->UnregisterVariable('tempo');

                $this->RegisterProfileIntegerEx('Speed.' . $this->InstanceID . '.Kodi', 'Intensity', '', '', [
                    [-1, $this->Translate('Back'), '', -1],
                    [0, 'Pause', '', 0x0000FF],
                    [1, $this->Translate('Forward'), '', 0x00FF00],
                ]);

                break;
        }
        $this->RegisterVariableBoolean('shuffled', $this->Translate('Shuffle'), '~Shuffle', 12);
        $this->EnableAction('shuffled');

        $this->RegisterVariableString('label', 'Titel', '~Song', 14);
        $this->RegisterVariableInteger('Status', 'Status', '~PlaybackPreviousNext', 3);
        $this->EnableAction('Status');
        $this->RegisterVariableInteger('speed', $this->Translate('Speed'), 'Speed.' . $this->InstanceID . '.Kodi', 10);
        $this->RegisterVariableFloat('percentage', 'Position', '~Progress', 26);
        $this->EnableAction('percentage');

        if (($this->PlayerId != self::Picture) && $this->ReadPropertyBoolean(self::PropertyShowTempo)) {
            // Kodi bietet die Tempi 0,8 bis 1,5 an (Einstellung im Player-Menü)
            $this->RegisterProfileFloat(self::ProfileTempo, 'Speedo', '', ' x', 0.8, 1.5, 0.1, 1);
            $this->RegisterVariableFloat('tempo', $this->Translate('Tempo'), self::ProfileTempo, 34);
            $this->EnableAction('tempo');
        } else {
            $this->UnregisterVariable('tempo');
        }

        $this->SetCover('');

        parent::ApplyChanges();

        $this->getActivePlayer();

        if ($this->isActive) {
            $this->GetItemInternal();
        }
        $this->ReloadForm(); // Force refresh TestCenter
    }

    ################## PUBLIC
    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItemInternal'.
     * Holt sich die Daten des aktuellen wiedergegebenen Items, und bildet die Eigenschaften in IPS-Variablen ab.
     *
     * @access public
     */
    public function GetItemInternal(): void
    {
        if (!$this->isActive) {
            return;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(['playerid' => $this->PlayerId, 'properties' => self::$ItemList]);
        $raw = $this->SendDirect($KodiData);

        if (is_null($raw)) {
            return;
        }
        $ret = $raw->item;
        if (is_null($ret)) {
            return;
        }
        switch ($this->PlayerId) {
            case self::Audio:
                $this->SetValueString('label', $ret->label);
                if (property_exists($ret, 'displayartist')) {
                    $this->SetValueString('artist', $ret->displayartist);
                } else {
                    if (property_exists($ret, 'artist')) {
                        if (is_array($ret->artist)) {
                            $this->SetValueString('artist', implode(', ', $ret->artist));
                        } else {
                            $this->SetValueString('artist', $ret->artist);
                        }
                    } else {
                        if (property_exists($ret, 'albumartist')) {
                            if (is_array($ret->albumartist)) {
                                $this->SetValueString('artist', implode(', ', $ret->albumartist));
                            } else {
                                $this->SetValueString('artist', $ret->albumartist);
                            }
                        } else {
                            $this->SetValueString('artist', '');
                        }
                    }

                }
                if (property_exists($ret, 'genre')) {
                    if (is_array($ret->genre)) {
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    } else {
                        $this->SetValueString('genre', $ret->genre);
                    }
                } else {
                    $this->SetValueString('genre', '');
                }

                if (property_exists($ret, 'album')) {
                    $this->SetValueString('album', $ret->album);
                } else {
                    $this->SetValueString('album', '');
                }

                if (property_exists($ret, 'year')) {
                    $this->SetValueInteger('year', $ret->year);
                } else {
                    $this->SetValueInteger('year', 0);
                }

                if (property_exists($ret, 'track')) {
                    $this->SetValueInteger('track', $ret->track);
                } else {
                    $this->SetValueInteger('track', 0);
                }

                if (property_exists($ret, 'disc')) {
                    $this->SetValueInteger('disc', $ret->disc);
                } else {
                    $this->SetValueInteger('disc', 0);
                }

                if (property_exists($ret, 'duration')) {
                    $this->SetValueString('duration', $this->ConvertTime($ret->duration));
                } else {
                    $this->SetValueString('duration', '');
                }

                if (property_exists($ret, 'lyrics')) {
                    $this->SetValueString('lyrics', $ret->lyrics);
                } else {
                    $this->SetValueString('lyrics', '');
                }

                switch ($this->ReadPropertyString(self::PropertyCoverTyp)) {
                    case 'album':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'album.thumb')) {
                                if ($ret->art->{'album.thumb'} != '') {
                                    $this->SetCover($ret->art->{'album.thumb'});
                                    break;
                                }
                            }
                        }
                        // Keine Grafik bei artist gefunden, somit greift artist.
                        // No break. Add additional comment above this line if intentional
                    case 'artist':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'artist.fanart')) {
                                if ($ret->art->{'artist.fanart'} != '') {
                                    $this->SetCover($ret->art->{'artist.fanart'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'fanart')) {
                            if ($ret->fanart != '') {
                                $this->SetCover($ret->fanart);
                                break;
                            }
                        }
                        // Keine Grafik bei artist gefunden, somit greift default.
                        // No break. Add additional comment above this line if intentional
                    default:
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');
                        break;
                }

                break;
            case self::Video:

                if (property_exists($ret, 'showtitle')) {
                    $this->SetValueString('showtitle', $ret->showtitle);
                } else {
                    $this->SetValueString('showtitle', '');
                }

                if (property_exists($ret, 'title')) {
                    $this->SetValueString('label', $ret->title);
                } else {
                    $this->SetValueString('label', $ret->label);
                }

                if (property_exists($ret, 'channel')) {
                    $this->SetValueString('channel', $ret->channel);
                } else {
                    $this->SetValueString('channel', '');
                }

                if (property_exists($ret, 'season')) {
                    $this->SetValueInteger('season', $ret->season);
                } else {
                    $this->SetValueInteger('season', -1);
                }

                if (property_exists($ret, 'episode')) {
                    $this->SetValueInteger('episode', $ret->episode);
                } else {
                    $this->SetValueInteger('episode', -1);
                }

                if (property_exists($ret, 'genre')) {
                    if (is_array($ret->genre)) {
                        $this->SetValueString('genre', implode(', ', $ret->genre));
                    } else {
                        $this->SetValueString('genre', $ret->genre);
                    }
                } else {
                    $this->SetValueString('genre', '');
                }

                if (property_exists($ret, 'runtime')) {
                    $this->SetValueString('duration', $this->ConvertTime($ret->runtime));
                } else {
                    $this->SetValueString('duration', '');
                }

                if (property_exists($ret, 'year')) {
                    $this->SetValueInteger('year', $ret->year);
                } else {
                    $this->SetValueInteger('year', 0);
                }

                if (property_exists($ret, 'plot')) {
                    $this->SetValueString('plot', $ret->plot);
                } else {
                    $this->SetValueString('plot', '');
                }

                switch ($this->ReadPropertyString(self::PropertyCoverTyp)) {
                    case 'fanart':
                        if (property_exists($ret, 'fanart')) {
                            if ($ret->fanart != '') {
                                $this->SetCover($ret->fanart);
                                break;
                            }
                        }
                        //No break, wenn kein Fanart, dann weiter mit poster
                        // No break. Add additional comment above this line if intentional
                    case 'poster':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.poster')) {
                                if ($ret->art->{'tvshow.poster'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster')) {
                                if ($ret->art->{'poster'} != '') {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster')) {
                            if ($ret->poster != '') {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.banner')) {
                                if ($ret->art->{'tvshow.banner'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner')) {
                            if ($ret->banner != '') {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');

                        break;
                    case 'banner':
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.banner')) {
                                if ($ret->art->{'tvshow.banner'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.banner'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'banner')) {
                            if ($ret->banner != '') {
                                $this->SetCover($ret->banner);
                                break;
                            }
                        }
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'tvshow.poster')) {
                                if ($ret->art->{'tvshow.poster'} != '') {
                                    $this->SetCover($ret->art->{'tvshow.poster'});
                                    break;
                                }
                            }
                            if (property_exists($ret->art, 'poster')) {
                                if ($ret->art->{'poster'} != '') {
                                    $this->SetCover($ret->art->{'poster'});
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'poster')) {
                            if ($ret->poster != '') {
                                $this->SetCover($ret->poster);
                                break;
                            }
                        }
                        // Keine Grafik bei Banner gefunden, somit greift default.
                        // No break. Add additional comment above this line if intentional
                    default:
                        if (property_exists($ret, 'art')) {
                            if (property_exists($ret->art, 'thumb')) {
                                if ($ret->art->thumb != '') {
                                    $this->SetCover($ret->art->thumb);
                                    break;
                                }
                            }
                        }
                        if (property_exists($ret, 'thumbnail')) {
                            if ($ret->thumbnail != '') {
                                $this->SetCover($ret->thumbnail);
                                break;
                            }
                        }
                        $this->SetCover('');

                        break;
                }
                break;
            case self::Picture:
                if (property_exists($ret, 'label')) {
                    $label = (string) $ret->label;
                } elseif (property_exists($ret, 'file')) {
                    $parts = explode('/', (string) $ret->file);
                    $label = array_pop($parts);
                } else {
                    $label = '';
                }

                $this->SetValueString('label', $label);
                if (property_exists($ret, 'art')) {
                    if (property_exists($ret->art, 'thumb')) {
                        if ($ret->art->thumb != '') {
                            $this->SetCover($ret->art->thumb);
                        } else {
                            $this->SetCover('');
                        }
                    }
                } else {
                    if (property_exists($ret, 'thumbnail')) {
                        if ($ret->thumbnail != '') {
                            $this->SetCover($ret->thumbnail);
                        }
                    } else {
                        $this->SetCover('');
                    }
                }
                break;
        }
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetItem'.
     * Holt die Daten des aktuellen wiedergegebenen Items, und gibt Diese als Array zurück.
     *
     * @access public
     * @return array|bool Das Array mit den Eigenschaften des Item, im Fehlerfall false
     */
    public function GetItem(): false|array
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetItem(['playerid' => $this->PlayerId, 'properties' => self::$ItemList]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret->item);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSubtitle'.
     * Deaktiviert oder aktiviert einen Untertitel
     *
     * @access public
     * @param int $Value Index des zu aktivierenden Untertitels, -1 für keinen.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetSubtitle(int $Value): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        if ($Value == -1) {
            $KodiData->SetSubtitle(['playerid' => $this->PlayerId, 'subtitle' => 'off']);
        } else {
            $KodiData->SetSubtitle(['playerid' => $this->PlayerId, 'subtitle' => $Value, 'enable' => true]);
        }
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            $this->SetValueInteger('subtitle', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set subtitle.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetAudioStream'.
     * Aktiviert einen bestimmten Audiostream.
     *
     * @access public
     * @param int $Value Index des zu aktivierenden Audiostream.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    public function SetAudioStream(int $Value): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAudioStream(['playerid' => $this->PlayerId, 'stream' => $Value]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on set audiostream.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetVideoStream'.
     * Wechselt die Videospur (nur bei Medien mit mehreren Videospuren).
     *
     * @access public
     * @param int $Value Index der Videospur (Variable „Videospur“ bzw. KODIPLAYER_GetItem).
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetVideoStream(int $Value): bool
    {
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetVideoStream(['playerid' => $this->PlayerId, 'stream' => $Value]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            $this->SetValueInteger('videostream', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set videostream.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_AddSubtitle'.
     * Lädt eine zusätzliche Untertiteldatei zur aktuellen Wiedergabe.
     *
     * @access public
     * @param string $Subtitle Lokaler Pfad (aus Sicht von Kodi) oder URL der Untertiteldatei.
     * @return bool True bei Erfolg, sonst false.
     */
    public function AddSubtitle(string $Subtitle): bool
    {
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->AddSubtitle(['playerid' => $this->PlayerId, 'subtitle' => $Subtitle]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on add subtitle.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetAudioDelay'.
     * Setzt die Verzögerung des Tons zum Bild.
     *
     * @access public
     * @param float $Value Verzögerung in Sekunden, Vielfache von 0,025. Standardbereich von Kodi -10 bis 10.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetAudioDelay(float $Value): bool
    {
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetAudioDelay(['playerid' => $this->PlayerId, 'offset' => round($Value / 0.025) * 0.025]);
        $ret = $this->Send($KodiData);
        if (is_object($ret) && property_exists($ret, 'offset')) {
            $this->SetValueFloat('audiodelay', (float) $ret->offset);
            return true;
        }
        trigger_error($this->Translate('Error on set audio delay.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetAudioDelay'.
     * Liest die aktuelle Verzögerung des Tons zum Bild.
     *
     * @access public
     * @return false|float Verzögerung in Sekunden oder false bei Fehler.
     */
    public function GetAudioDelay(): false|float
    {
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetAudioDelay();
        $ret = $this->SendDirect($KodiData);
        if (is_object($ret) && property_exists($ret, 'offset')) {
            $this->SetValueFloat('audiodelay', (float) $ret->offset);
            return (float) $ret->offset;
        }
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetViewMode'.
     * Setzt das Bildformat der Videowiedergabe.
     *
     * @access public
     * @param string $Value Bildformat: normal, zoom, stretch4x3, widezoom, stretch16x9, original, stretch16x9nonlin, zoom120width oder zoom110width.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetViewMode(string $Value): bool
    {
        $Index = array_search($Value, self::ViewModes, true);
        if ($Index === false) {
            trigger_error(sprintf($this->Translate('Unknown view mode. Valid values: %s'), implode(', ', self::ViewModes)), E_USER_NOTICE);
            return false;
        }
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetViewMode(['viewmode' => $Value]);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            $this->SetValueInteger('viewmode', $Index);
            return true;
        }
        trigger_error($this->Translate('Error on set view mode.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetViewMode'.
     * Liest das Bildformat und die Bildeinstellungen der Videowiedergabe.
     *
     * @access public
     * @return array|false Array mit viewmode, zoom, pixelratio, verticalshift und nonlinearstretch oder false bei Fehler.
     */
    public function GetViewMode(): false|array
    {
        if (!$this->CheckVideoPlayer()) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetViewMode();
        $ret = $this->SendDirect($KodiData);
        if (!is_object($ret) || !property_exists($ret, 'viewmode')) {
            return false;
        }
        $Index = array_search($ret->viewmode, self::ViewModes, true);
        if ($Index !== false) {
            $this->SetValueInteger('viewmode', $Index);
        }
        return $KodiData->ToArray($ret);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetTempo'.
     * Setzt das Wiedergabetempo mit Tonhöhenkorrektur.
     *
     * @access public
     * @param float $Value Tempo, z.B. 1.0 für normal. Kodi bietet 0.8 bis 1.5 an.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetTempo(float $Value): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetTempo(['playerid' => $this->PlayerId, 'tempo' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_object($ret) && property_exists($ret, 'tempo')) {
            // Kodi meldet das tatsächlich gesetzte Tempo zurück, abfragen lässt es sich nicht.
            $this->SetValueFloat('tempo', (float) $ret->tempo);
            return true;
        }
        trigger_error($this->Translate('Error on set tempo.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Play'.
     * Starte die Wiedergabe des aktuelle pausierten Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Play(): bool
    {
        if (!$this->isActive) {
            if (!$this->LoadPlaylist()) {
                trigger_error($this->Translate('Error on send play.'), E_USER_NOTICE);
                return false;
            }
            return true;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(['playerid' => $this->PlayerId, 'play' => true]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->speed === 1) {
            $this->SetValueInteger('Status', 2);
            return true;
        }
        trigger_error($this->Translate('Error on send play.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Pause'.
     * Pausiert die Wiedergabe des aktuellen Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Pause(): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->PlayPause(['playerid' => $this->PlayerId, 'play' => false]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret->speed === 0) {
            $this->SetValueInteger('Status', 3);
            return true;
        }
        trigger_error($this->Translate('Error on send pause.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Stop'.
     * Stoppt die Wiedergabe des aktuellen Items.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function Stop(): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Stop(['playerid' => $this->PlayerId]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueInteger('Status', 1);
            return true;
        }
        trigger_error($this->Translate('Error on send stop.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToNext'.
     * Springt zum nächsten Item in der Wiedergabeliste.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToNext(): bool
    {
        return $this->GoToValue('next');
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToPrevious'.
     * Springt zum vorherigen Item in der Wiedergabeliste.
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToPrevious(): bool
    {
        return $this->GoToValue('previous');
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GoToTrack'.
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     *
     * @access public
     * @param int $Value Nummer des Titels in der Wiedergabeliste (1 = erster Eintrag).
     * @return bool True bei Erfolg, sonst false.
     */
    public function GoToTrack(int $Value): bool
    {
        if ($Value < 1) {
            trigger_error($this->Translate('Track number must be 1 or higher.'), E_USER_NOTICE);
            return false;
        }
        // Player.GoTo zählt ab 0
        return $this->GoToValue($Value - 1);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetShuffle'.
     * Setzt den Zufallsmodus.
     *
     * @access public
     * @param bool $Value True für Zufallswiedergabe aktiv, false für desaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetShuffle(bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetShuffle(['playerid' => $this->PlayerId, 'shuffle' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('shuffled', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set shuffle.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetRepeat'.
     * Setzten den Wiederholungsmodus.
     *
     * @access public
     * @param int $Value Modus der Wiederholung.
     *   enum[0=aus, 1=Titel, 2=Alle]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetRepeat(int $Value): bool
    {
        if (($Value < 0) || ($Value > 2)) {
            trigger_error(sprintf($this->Translate('%s must be between %d and %d.'), 'Value', 0, 2), E_USER_NOTICE);
            return false;
        }

        $repeat = ['off', 'one', 'all'];
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetRepeat(['playerid' => $this->PlayerId, 'repeat' => $repeat[$Value]]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            if ($Value == 1) {
                $Value = 2;
            } elseif ($Value == 2) {
                $Value = 1;
            }
            $this->SetValueInteger('repeat', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set repeat.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Zoom'.
     * Setzt die Zoomstufe des aktuellen Bildes (nur Bilder-Player).
     *
     * @access public
     * @param int $Level Zoomstufe 1 (kein Zoom) bis 10.
     * @return bool True bei Erfolg, sonst false.
     */
    public function Zoom(int $Level): bool
    {
        if (($Level < 1) || ($Level > 10)) {
            trigger_error(sprintf($this->Translate('%s must be between %d and %d.'), 'Level', 1, 10), E_USER_NOTICE);
            return false;
        }
        return $this->SendPictureCommand('Zoom', ['zoom' => $Level]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_ZoomIn'.
     * Vergrößert das aktuelle Bild um eine Stufe (nur Bilder-Player).
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function ZoomIn(): bool
    {
        return $this->SendPictureCommand('Zoom', ['zoom' => 'in']);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_ZoomOut'.
     * Verkleinert das aktuelle Bild um eine Stufe (nur Bilder-Player).
     *
     * @access public
     * @return bool True bei Erfolg, sonst false.
     */
    public function ZoomOut(): bool
    {
        return $this->SendPictureCommand('Zoom', ['zoom' => 'out']);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Rotate'.
     * Dreht das aktuelle Bild um 90 Grad (nur Bilder-Player).
     *
     * @access public
     * @param bool $Clockwise True im Uhrzeigersinn, false gegen den Uhrzeigersinn.
     * @return bool True bei Erfolg, sonst false.
     */
    public function Rotate(bool $Clockwise): bool
    {
        return $this->SendPictureCommand('Rotate', ['value' => $Clockwise ? 'clockwise' : 'counterclockwise']);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_Move'.
     * Verschiebt den sichtbaren Ausschnitt des gezoomten Bildes (nur Bilder-Player).
     *
     * @access public
     * @param string $Direction Richtung: 'left', 'right', 'up' oder 'down'.
     * @return bool True bei Erfolg, sonst false.
     */
    public function Move(string $Direction): bool
    {
        $Direction = strtolower($Direction);
        if (!in_array($Direction, ['left', 'right', 'up', 'down'], true)) {
            trigger_error(sprintf($this->Translate('Invalid direction: %s'), $Direction), E_USER_NOTICE);
            return false;
        }
        return $this->SendPictureCommand('Move', ['direction' => $Direction]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPartymode'.
     * Setzt den Partymodus.
     *
     * @access public
     * @param bool $Value True für Partymodus aktiv, false für desaktiv.
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPartymode(bool $Value): bool
    {
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetPartymode(['playerid' => $this->PlayerId, 'partymode' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            $this->SetValueBoolean('partymode', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set partymode.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetSpeed'.
     * Setzten die Abspielgeschwindigkeit.
     *
     * @access public
     * @param int $Value Geschwindigkeit.
     *   enum[-32, -16, -8, -4, -2, 0, 1, 2, 4, 8, 16, 32]
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetSpeed(int $Value): bool
    {
        if ($Value == 1) {
            return $this->Play();
        }

        if ($Value == 0) {
            return $this->Pause();
        }

        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }

        if (!in_array($Value, [-32, -16, -8, -4, -2, -1, 0, 1, 2, 4, 8, 16, 32])) {
            trigger_error($this->Translate('Invalid Value for speed.'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->SetSpeed(['playerid' => $this->PlayerId, 'speed' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ((int) $ret->speed == $Value) {
            $this->SetValueInteger('speed', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set speed.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_SetPosition'.
     * Springt auf eine absolute Position innerhalb einer Wiedergabe.
     *
     * @access public
     * @param int $Value Position in Prozent
     * @return bool True bei Erfolg, sonst false.
     */
    public function SetPosition(int $Value): bool
    {
        if (($Value < 0) || ($Value > 100)) {
            trigger_error(sprintf($this->Translate('%s must be between %d and %d.'), 'Value', 0, 100), E_USER_NOTICE);
            return false;
        }

        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }

        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Seek(['playerid' => $this->PlayerId, 'value' => ['percentage' => $Value]]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if (($this->PlayerId == self::Audio) || (round($ret->percentage) == $Value)) {
            $this->SetValueFloat('percentage', $Value);
            return true;
        }
        trigger_error($this->Translate('Error on set Position.'), E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_GetPlayers'.
     * Liefert alle Player von Kodi (intern, extern, UPnP-Renderer).
     *
     * @access public
     * @param string $Media Filter: 'all', 'video' oder 'audio'.
     * @return array|bool Array mit den Playern (name, type, playsvideo, playsaudio) oder false bei Fehler.
     */
    public function GetPlayers(string $Media): false|array
    {
        if (!in_array($Media, ['all', 'video', 'audio'], true)) {
            trigger_error(sprintf($this->Translate('Invalid type: %s'), $Media), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GetPlayers(['media' => $Media]);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            return false;
        }
        return $KodiData->ToArray($ret);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadAlbum'.
     * Lädt ein Album und startet die Wiedergabe.
     *
     * @access public
     * @param int $AlbumId ID des Album.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadAlbum(int $AlbumId): bool
    {
        return $this->Load('albumid', $AlbumId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadArtist'.
     * Lädt alle Item eines Artist und startet die Wiedergabe.
     *
     * @access public
     * @param int $ArtistId ID des Artist.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadArtist(int $ArtistId): bool
    {
        return $this->Load('artistid', $ArtistId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadChannel'.
     * Lädt einen PVR-Kanal und startet die Wiedergabe.
     *
     * @access public
     * @param int $ChannelId ID des Kanals.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadChannel(int $ChannelId): bool
    {
        return $this->Load('channelid', $ChannelId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadDirectory'.
     * Lädt alle Item eines Verzeichnisses und startet die Wiedergabe.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadDirectory(string $Directory): bool
    {
        return $this->Load('directory', $Directory);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadDirectoryRecursive'.
     * Lädt alle Item eines Verzeichnisses, sowie dessen Unterverzeichnisse, und startet die Wiedergabe.
     *
     * @access public
     * @param string $Directory Pfad welcher hinzugefügt werden soll.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadDirectoryRecursive(string $Directory): bool
    {
        return $this->Load('directory', $Directory, ['recursive' => true]);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadEpisode'.
     * Lädt eine Episode und startet die Wiedergabe.
     *
     * @access public
     * @param int $EpisodeId ID der Episode.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadEpisode(int $EpisodeId): bool
    {
        return $this->Load('episodeid', $EpisodeId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadFile'.
     * Lädt eine Datei und startet die Wiedergabe.
     *
     * @access public
     * @param string $File Pfad zu einer Datei.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */ public function LoadFile(string $File): bool
    {
        return $this->Load('file', $File);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadGenre'.
     * Lädt eine komplettes Genre und startet die Wiedergabe.
     *
     * @access public
     * @param int $GenreId ID des Genres.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadGenre(int $GenreId): bool
    {
        return $this->Load('genreid', $GenreId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadMovie'.
     * Lädt ein Film und startet die Wiedergabe.
     *
     * @access public
     * @param int $MovieId ID des Filmes.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadMovie(int $MovieId): bool
    {
        return $this->Load('movieid', $MovieId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadMusicvideo'.
     * Lädt ein Musikvideo und startet die Wiedergabe.
     *
     * @access public
     * @param int $MusicvideoId ID des Musikvideos.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadMusicvideo(int $MusicvideoId): bool
    {
        return $this->Load('musicvideoid', $MusicvideoId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadPlaylist'.
     * Lädt die Playlist und startet die Wiedergabe.
     *
     * @access public
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadPlaylist(): bool
    {
        return $this->Load('playlistid', $this->PlayerId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadRecording'.
     * Lädt eine Aufzeichnung und startet die Wiedergabe.
     *
     * @access public
     * @param int $RecordingId ID der Aufnahme.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadRecording(int $RecordingId): bool
    {
        return $this->Load('recordingid', $RecordingId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadSong'.
     * Lädt ein Songs und startet die Wiedergabe.
     *
     * @access public
     * @param int $SongId ID des Songs.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadSong(int $SongId): bool
    {
        return $this->Load('songid', $SongId);
    }

    /**
     * IPS-Instanz-Funktion 'KODIPLAYER_LoadEx'.
     * Lädt ein Medium mit Optionen für Zufall, Wiederholung und Fortsetzen und startet die Wiedergabe.
     *
     * @access public
     * @param string $Type Medientyp: Album, Artist, Genre, Song, Movie, Episode, Musicvideo, Channel, Recording, File, Directory, DirectoryRecursive oder Playlist.
     * @param string $Value ID aus der Kodi-Datenbank oder Pfad. Bei Playlist die Startposition ab 1 (leer = Anfang).
     * @param int $Shuffle Zufallswiedergabe: -1 = unverändert, 0 = aus, 1 = an.
     * @param int $Repeat Wiederholung: -1 = unverändert, 0 = aus, 1 = Titel, 2 = Alle (wie SetRepeat).
     * @param bool $Resume True, um ab dem gespeicherten Fortsetzungspunkt zu starten.
     * @return bool TRUE bei erfolgreicher Ausführung, sonst FALSE.
     */
    public function LoadEx(string $Type, string $Value, int $Shuffle, int $Repeat, bool $Resume): bool
    {
        if (!array_key_exists($Type, self::LoadTypes)) {
            trigger_error(sprintf($this->Translate('Invalid media type: %s'), $Type), E_USER_NOTICE);
            return false;
        }
        if (($Shuffle < -1) || ($Shuffle > 1)) {
            trigger_error(sprintf($this->Translate('%s must be between %d and %d.'), 'Shuffle', -1, 1), E_USER_NOTICE);
            return false;
        }
        if (($Repeat < -1) || ($Repeat > 2)) {
            trigger_error(sprintf($this->Translate('%s must be between %d and %d.'), 'Repeat', -1, 2), E_USER_NOTICE);
            return false;
        }
        [$Field, $IsId, $Ext] = self::LoadTypes[$Type];
        $Value = trim($Value);
        if ($Type == 'Playlist') {
            $ItemValue = $this->PlayerId;
            if ($Value !== '') {
                if (!ctype_digit($Value) || ((int) $Value < 1)) {
                    trigger_error($this->Translate('Track number must be 1 or higher.'), E_USER_NOTICE);
                    return false;
                }
                $Ext['position'] = (int) $Value - 1;
            }
        } elseif ($IsId) {
            if (!ctype_digit($Value)) {
                trigger_error($this->Translate('Value must be a numeric ID.'), E_USER_NOTICE);
                return false;
            }
            $ItemValue = (int) $Value;
        } else {
            if ($Value === '') {
                trigger_error($this->Translate('Path must not be empty.'), E_USER_NOTICE);
                return false;
            }
            $ItemValue = $Value;
        }
        $Options = ['resume' => $Resume];
        if ($Shuffle != -1) {
            $Options['shuffled'] = ($Shuffle == 1);
        }
        if ($Repeat != -1) {
            $Options['repeat'] = ['off', 'one', 'all'][$Repeat];
        }
        return $this->Load($Field, $ItemValue, $Ext, $Options);
    }

    ################## ActionHandler
    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    protected function ActionHandler(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'Status':
                switch ((int) $Value) {
                    case 0: //Prev
                        $this->GoToPrevious();
                        return;
                    case 1: //Stop
                        $this->Stop();
                        return;
                    case 2: //Play
                        $this->Play();
                        return;
                    case 3: //Pause
                        $this->Pause();
                        return;
                    case 4: //Next
                        $this->GoToNext();
                        return;
                }
                trigger_error($this->Translate('Invalid Value.'), E_USER_NOTICE);
                return;
            case 'shuffled':
                $this->SetShuffle((bool) $Value);
                return;
            case 'repeat':
                if ((int) $Value == 1) {
                    $Value = 2;
                } elseif ((int) $Value == 2) {
                    $Value = 1;
                }
                $this->SetRepeat((int) $Value);
                return;
            case 'speed':
                $this->SetSpeed((int) $Value);
                return;
            case 'partymode':
                $this->SetPartymode((bool) $Value);
                return;
            case 'percentage':
                $this->SetPosition((int) $Value);
                return;
            case 'subtitle':
                $this->SetSubtitle((int) $Value);
                return;
            case 'audiostream':
                $this->SetAudioStream((int) $Value);
                return;
            case 'videostream':
                $this->SetVideoStream((int) $Value);
                return;
            case 'audiodelay':
                $this->SetAudioDelay((float) $Value);
                return;
            case 'viewmode':
                if (!isset(self::ViewModes[(int) $Value])) {
                    trigger_error($this->Translate('Invalid Value.'), E_USER_NOTICE);
                    return;
                }
                $this->SetViewMode(self::ViewModes[(int) $Value]);
                return;
            case 'tempo':
                $this->SetTempo((float) $Value);
                return;
            case self::ActionCreateTileLinks:
                $this->CreateTileLinks();
                return;
            case self::ActionRemoveTileLinks:
                $this->RemoveTileLinks();
                return;
            default:
                trigger_error($this->Translate('Invalid Ident.'), E_USER_NOTICE);
                return;
        }
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState(int $State): void
    {
        parent::IOChangeState($State);
        if ($State == IS_ACTIVE) {
            $this->isActive = true;
            //todo RequestAction GetItemInternal wird private
            IPS_RunScriptText('<? KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
            IPS_RunScriptText('<? KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
        } else {
            $this->isActive = false;
            $this->SetTimerInterval(self::TimerName, 0);
            $this->SetValueInteger('Status', 1);
            $this->SetValueString('duration', '');
            $this->SetValueString('time', '');
            $this->SetValueFloat('percentage', 0);
            $this->SetValueInteger('speed', 0);
        }
    }

    /**
     * Werte der Eigenschaften anfragen.
     *
     * @access protected
     * @param array $Params Enthält den Index "properties", in welchen alle anzufragenden Eigenschaften als Array enthalten sind.
     * @return bool true bei erfolgreicher Ausführung und dekodierung, sonst false.
     */
    protected function RequestProperties(array $Params): bool
    {
        $Param = array_merge($Params, ['playerid' => $this->PlayerId]);
        if (!$this->isActive) {
            return false;
        }
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetProperties($Param);
        $ret = $this->SendDirect($KodiData);
        if (is_null($ret)) {
            $KodiPayload = new stdClass();
            $KodiPayload->player = new stdClass();
            $KodiPayload->player->playerid = $this->PlayerId;
            $this->Decode('OnStop', $KodiPayload);
            return false;
        }
        $this->Decode('GetProperties', $ret);
        if (in_array('videostreams', $Params['properties'])) {
            $this->RequestVideoSettings();
        }
        return true;
    }

    /**
     * Dekodiert die empfangenen Daten und führt die Statusvariablen nach.
     *
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(string $Method, mixed $KodiPayload): void
    {
        if ($KodiPayload == null) {
            $this->SendDebug('NULL : ' . $Method, $KodiPayload, 0);
            return;
        }
        if (property_exists($KodiPayload, 'player')) {
            if ($KodiPayload->player->playerid != $this->PlayerId) {
                return;
            }
        } else {
            if (property_exists($KodiPayload, 'type')) {
                if (self::$Playertype[(string) $KodiPayload->type] != $this->PlayerId) {
                    return;
                }
            } else {
                if (property_exists($KodiPayload, 'item')) {
                    if (property_exists($KodiPayload->item, 'channeltype')) {
                        if (self::$Playertype[(string) $KodiPayload->item->channeltype] != $this->PlayerId) {
                            return;
                        }
                    } else {
                        if (self::$Playertype[(string) $KodiPayload->item->type] != $this->PlayerId) {
                            return;
                        }
                    }
                }
            }
        }
        $this->SendDebug($Method, $KodiPayload, 0);

        switch ($Method) {
            case 'GetProperties':
            case 'OnPropertyChanged':
                foreach ($KodiPayload as $param => $value) {
                    switch ($param) {
                        case 'subtitles':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            $this->CreateSubtitleProfil($value);
                            if (count($value) == 0) {
                                $this->DisableAction('subtitle');
                            } else {
                                $this->EnableAction('subtitle');
                            }
                            break;
                        case 'subtitleenabled':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if ($value === false) {
                                $this->SetValueInteger('subtitle', -1);
                            }
                            break;
                            // Object
                        case 'currentsubtitle':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if (is_object($value)) {
                                if (property_exists($value, 'index')) {
                                    $this->SetValueInteger('subtitle', (int) $value->index);
                                }
                            }
                            break;
                        case 'audiostreams':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            $this->CreateAudioProfil($value);
                            if (count($value) == 1) {
                                $this->DisableAction('audiostream');
                            } else {
                                $this->EnableAction('audiostream');
                            }
                            break;
                        case 'currentaudiostream':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if (is_object($value)) {
                                if (property_exists($value, 'index')) {
                                    $this->SetValueInteger('audiostream', (int) $value->index);
                                } else {
                                    $this->SetValueInteger('audiostream', 0);
                                }
                            } else {
                                $this->SetValueInteger('audiostream', 0);
                            }
                            break;
                        case 'videostreams':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            $this->CreateVideoProfil($value);
                            if (count($value) > 1) {
                                $this->EnableAction('videostream');
                            } else {
                                $this->DisableAction('videostream');
                            }
                            break;
                        case 'currentvideostream':
                            if ($this->PlayerId != self::Video) {
                                break;
                            }
                            if (is_object($value) && property_exists($value, 'index')) {
                                $this->SetValueInteger('videostream', (int) $value->index);
                            } else {
                                $this->SetValueInteger('videostream', 0);
                            }
                            break;
                            //time
                        case 'totaltime':
                            $this->SetValueString('duration', $this->ConvertTime($value));
                            break;
                        case 'time':
                            $this->SetValueString($param, $this->ConvertTime($value));
                            break;
                            // Anzahl

                        case 'repeat': //off
                            if ($this->PlayerId != self::Picture) {
                                $this->SetValueInteger($param, array_search((string) $value, ['off', 'all', 'one']));
                            }
                            break;
                            //boolean
                        case 'shuffled':
                        case 'partymode':
                            $this->SetValueBoolean($param, (bool) $value);
                            break;
                            //integer
                        case 'speed':
                            $this->SetValueInteger('speed', (int) $value);
                            if ((int) $value == 0) {
                                $this->SetValueInteger('Status', 3);
                            } else {
                                $this->SetValueInteger('Status', 2);
                            }
                            break;
                        case 'percentage':
                            $this->SetValueFloat($param, ((float) $value));
                            break;

                            //Action en/disable
                        case 'canseek':
                            if ((bool) $value) {
                                $this->EnableAction('percentage');
                            } else {
                                $this->DisableAction('percentage');
                            }
                            break;
                        case 'canshuffle':
                            if ((bool) $value) {
                                $this->EnableAction('shuffled');
                            } else {
                                $this->DisableAction('shuffled');
                            }
                            break;
                        case 'canrepeat':
                            if ($this->PlayerId == self::Picture) {
                                break;
                            }
                            if ((bool) $value) {
                                $this->EnableAction('repeat');
                            } else {
                                $this->DisableAction('repeat');
                            }
                            break;

                        case 'canchangespeed':
                            if ((bool) $value) {
                                $this->EnableAction('speed');
                            } else {
                                $this->DisableAction('speed');
                            }
                            break;
                            //ignore
                        case 'canrotate':
                        case 'canzoom':
                        case 'canmove':
                        case 'playlistid':
                        case 'live':
                        case 'cachepercentage':
                        case 'playlist':
                        case 'position':
                        case 'type':
                            break;
                        default:
                            $this->SendDebug('Todo:' . $param, $value, 0);
                            break;
                    }
                }
                break;
            case 'OnStop':
                $this->SetTimerInterval(self::TimerName, 0);
                $this->SetValueInteger('Status', 1);
                $this->SetValueString('duration', '');
                $this->SetValueString('time', '');
                $this->SetValueFloat('percentage', 0);
                $this->SetValueInteger('speed', 0);

                $this->SetValueString('album', '');
                $this->SetValueInteger('track', 0);
                $this->SetValueInteger('disc', 0);
                $this->SetValueString('artist', '');
                $this->SetValueString('lyrics', '');
                $this->SetValueBoolean('partymode', false);
                $this->SetValueInteger('year', 0);
                $this->SetValueString('genre', '');

                $this->SetValueString('showtitle', '');
                $this->SetValueString('channel', '');
                // -1 wie bei GetItemInternal und Kodi selbst: keine Staffel/Episode
                $this->SetValueInteger('season', -1);
                $this->SetValueInteger('episode', -1);

                $this->SetValueString('plot', '');
                $this->SetValueInteger('audiostream', 0);
                $this->SetValueInteger('subtitle', 0);
                $this->SetValueInteger('videostream', 0);
                // Annahme (nicht live geprüft): Kodi startet die nächste Wiedergabe mit Tempo 1,0
                $this->SetValueFloat('tempo', 1.0);

                $this->SetValueInteger('repeat', 0);
                $this->SetValueBoolean('shuffled', false);

                $this->SetValueString('label', '');

                $this->setActivePlayer(false);
                $this->SetCover('');
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                //todo RequestAction GetItemInternal wird private
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
                break;
            case 'OnPlay':
            case 'OnResume':
                $this->setActivePlayer(true);
                $this->SetValueInteger('Status', 2);
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                //todo RequestAction GetItemInternal wird private
                IPS_RunScriptText('<? IPS_Sleep(750);  @KODIPLAYER_GetItemInternal(' . $this->InstanceID . ');');
                $this->SetTimerInterval(self::TimerName, 2000);
                break;
            case 'OnPause':
                $this->SetTimerInterval(self::TimerName, 0);
                $this->SetValueInteger('Status', 3);
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                break;
            case 'OnSeek':
                $this->SetValueString('time', $this->ConvertTime($KodiPayload->player->time));
                break;
            case 'OnAVStart':
            case 'OnAVChange':
                // Spuren stehen erst jetzt fest, daher Audio, Untertitel und Video neu lesen
                IPS_RunScriptText('<? @KODIPLAYER_RequestState(' . $this->InstanceID . ',"ALL");');
                break;
            case 'OnSpeedChanged':
                IPS_RunScriptText('<? IPS_Sleep(500); @KODIPLAYER_RequestState(' . $this->InstanceID . ',"speed");');
                break;
            default:
                $this->SendDebug($Method, $KodiPayload, 0);
                break;
        }
    }

    ################## PRIVATE
    /**
     * Übernimmt Statusvariablen früherer Versionen.
     * Gibt es eine kompatible aktuelle Variable, wird nur der Ident geändert (Objekt-ID, Name und Archivdaten bleiben erhalten).
     * Existiert die aktuelle Variable schon oder passt der Typ nicht, wird die alte Variable gelöscht.
     *
     * @access private
     */
    private function MigrateLegacyVariables(): void
    {
        foreach (self::LegacyVariablesRename as $OldIdent => $New) {
            $OldId = @$this->GetIDForIdent($OldIdent);
            if ($OldId === false) {
                continue;
            }
            $NewId = @$this->GetIDForIdent($New['Ident']);
            if (($NewId === false) && (IPS_GetVariable($OldId)['VariableType'] == $New['Type'])) {
                IPS_SetIdent($OldId, $New['Ident']);
                $this->SendDebug('Migrate', $OldIdent . ' => ' . $New['Ident'], 0);
                continue;
            }
            $this->UnregisterVariable($OldIdent);
            $this->SendDebug('Migrate', 'delete ' . $OldIdent, 0);
        }
        foreach (self::LegacyVariablesObsolete as $OldIdent) {
            if (@$this->GetIDForIdent($OldIdent) === false) {
                continue;
            }
            $this->UnregisterVariable($OldIdent);
            $this->SendDebug('Migrate', 'delete ' . $OldIdent, 0);
        }
    }

    /**
     * Legt unterhalb der Instanz Links auf Lautstärke und Stummschaltung der Application-Instanz
     * sowie auf die Playlist der passenden Playlist-Instanz an, damit die Mediaplayer-Kachel alle Elemente zeigt.
     * Gesucht wird nur bei Instanzen am selben Splitter. Bestehende Links erhalten das aktuelle Ziel.
     * Wird über eine Schaltfläche im Konfigurationsformular ausgelöst, Meldungen daher per echo.
     *
     * @access private
     */
    private function CreateTileLinks(): void
    {
        $SplitterId = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($SplitterId == 0) {
            echo $this->Translate('Instance has no parent.');
            return;
        }
        $Missing = [];
        foreach (self::TileLinks as $LinkIdent => $Link) {
            $TargetId = $this->FindTileLinkTarget($SplitterId, $Link['Module'], $Link['Ident']);
            if ($TargetId === false) {
                $Missing[] = $LinkIdent;
                continue;
            }
            $LinkId = @$this->GetIDForIdent($LinkIdent);
            if ($LinkId === false) {
                $LinkId = IPS_CreateLink();
                IPS_SetParent($LinkId, $this->InstanceID);
                IPS_SetIdent($LinkId, $LinkIdent);
                IPS_SetName($LinkId, IPS_GetName($TargetId));
                IPS_SetPosition($LinkId, $Link['Position']);
            }
            IPS_SetLinkTargetID($LinkId, $TargetId);
            $this->SendDebug('TileLink', $LinkIdent . ' => ' . $TargetId, 0);
        }
        if (count($Missing) > 0) {
            echo $this->Translate('Source variables not found. Create the Kodi Application and the matching Kodi Playlist instance first.');
            return;
        }
        echo $this->Translate('Links created.');
    }

    /**
     * Löscht die mit CreateTileLinks angelegten Links.
     *
     * @access private
     */
    private function RemoveTileLinks(): void
    {
        foreach (array_keys(self::TileLinks) as $LinkIdent) {
            $LinkId = @$this->GetIDForIdent($LinkIdent);
            if (($LinkId !== false) && IPS_LinkExists($LinkId)) {
                IPS_DeleteLink($LinkId);
            }
        }
        echo $this->Translate('Links removed.');
    }

    /**
     * Sucht die Zielvariable für einen Link der Mediaplayer-Kachel.
     *
     * @access private
     * @param int $SplitterId Splitter, an dem die Quell-Instanz hängen muss.
     * @param string $ModuleId GUID des Quell-Moduls.
     * @param string $Ident Ident der Zielvariable in der Quell-Instanz.
     * @return int|false ID der Variable oder false, wenn nicht gefunden.
     */
    private function FindTileLinkTarget(int $SplitterId, string $ModuleId, string $Ident): int|false
    {
        foreach (IPS_GetInstanceListByModuleID($ModuleId) as $InstanceId) {
            if (IPS_GetInstance($InstanceId)['ConnectionID'] != $SplitterId) {
                continue;
            }
            if (($ModuleId == self::GUIDPlaylist) && (IPS_GetProperty($InstanceId, 'PlaylistID') != $this->ReadPropertyInteger(self::PropertyPlayerID))) {
                continue;
            }
            $VariableId = @IPS_GetObjectIDByIdent($Ident, $InstanceId);
            if ($VariableId !== false) {
                return $VariableId;
            }
        }
        return false;
    }

    /**
     * Fragt Kodi an ob der Playertyp der Instanz gerade aktiv ist.
     *
     * @return bool true wenn Player aktiv ist, sonst false
     */
    private function getActivePlayer(): bool
    {
        $KodiData = new Kodi_RPC_Data(static::$Namespace);
        $KodiData->GetActivePlayers();
        $ret = @$this->SendDirect($KodiData);

        if (is_null($ret) || (count($ret) == 0)) {
            $this->isActive = false;
        } else {
            $this->isActive = ((int) $ret[0]->playerid == $this->PlayerId);
        }

        $this->SendDebug('getActivePlayer', $this->isActive, 0);

        return $this->isActive;
    }

    /**
     * Setzt die Eigenschaft isActive sowie die dazugehörige IPS-Variable.
     *
     * @access private
     * @param bool $isActive True wenn Player als aktive gesetzt werden soll, sonder false.
     */
    private function setActivePlayer(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->SendDebug('setActive', $this->isActive, 0);
    }

    /**
     * Holt das über $file übergebene Cover vom Kodi-Webinterface, skaliert und konvertiert dieses und speichert es in einem MedienObjekt ab.
     *
     * @access private
     * @param string $file
     */
    private function SetCover(string $file): void
    {
        $CoverID = @IPS_GetObjectIDByIdent('CoverIMG', $this->InstanceID);
        if ($CoverID === false) {
            $CoverID = IPS_CreateMedia(1);
            IPS_SetParent($CoverID, $this->InstanceID);
            IPS_SetIdent($CoverID, 'CoverIMG');
            IPS_SetName($CoverID, 'Cover');
            IPS_SetPosition($CoverID, 27);
            IPS_SetMediaCached($CoverID, true);
            $filename = 'media' . DIRECTORY_SEPARATOR . 'Cover_' . $this->InstanceID . '.png';
            IPS_SetMediaFile($CoverID, $filename, false);
            $this->SendDebug('Create Media', $filename, 0);
        }

        if ($file == '') {
            $CoverRAW = false;
        } else {
            $Size = $this->ReadPropertyInteger(self::PropertyCoverSize);
            $CoverRAW = $this->GetThumbnail($file, 0, $Size);
        }

        if ($CoverRAW === false) {
            $CoverRAW = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'nocover.png');
        }

        IPS_SetMediaContent($CoverID, base64_encode($CoverRAW));
    }

    /**
     *
     * Springt auf ein bestimmtes Item in der Wiedergabeliste.
     *
     * @access private
     * @param int|string $Value Index oder String-Enum
     *   enum['previous', 'next']
     * @return bool True bei Erfolg, sonst false.
     */
    private function GoToValue(int|string $Value): bool
    {
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->GoTo(['playerid' => $this->PlayerId, 'to' => $Value]);
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on send ') . $Value . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * Lädt ein Item und startet die Wiedergabe.
     *
     * @access private
     * @param string $ItemTyp Der Typ des Item.
     * @param mixed $ItemValue Der Wert des Item.
     * @param array $Ext Zusätzliche Felder des Item, z.B. ['recursive' => true] (optional).
     * @param array $Options Optionen von Player.Open (shuffled, repeat, resume) (optional).
     * @return bool True bei Erfolg. Sonst false.
     */
    private function Load(string $ItemTyp, mixed $ItemValue, array $Ext = [], array $Options = []): bool
    {
        $Params = ['item' => array_merge([$ItemTyp => $ItemValue], $Ext)];
        if (count($Options) > 0) {
            $Params['options'] = $Options;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace);
        $KodiData->Open($Params);
        $ret = $this->Send($KodiData);
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on load ') . $ItemTyp . '.', E_USER_NOTICE);
        return false;
    }

    /**
     * Erzeugt aus einem Objekt ein Array für ein IPS-Variablenprofil
     *
     * @return array Werteliste für das Variablenprofil.
     */
    private function CreateProfilArray(array $Data, array $Assoziation = []): array
    {
        foreach ($Data as $item) {
            if ($item->language == '') {
                if (property_exists($item, 'name')) {
                    $Assoziation[] = [$item->index, $item->name, '', -1];
                } else {
                    $Assoziation[] = [$item->index, 'Unbekannt', '', -1];
                }
            } else {
                if (property_exists($item, 'name')) {
                    $Assoziation[] = [$item->index, $item->language . ' - ' . $item->name, '', -1];
                } else {
                    $Assoziation[] = [$item->index, $item->language, '', -1];
                }
            }
        }
        return $Assoziation;
    }

    /**
     * Erzeugt aus einem Objekt von Untertiteln ein IPS-Variablenprofil.
     */
    private function CreateSubtitleProfil(array $Subtitles): void
    {
        $Assoziation[0] = [-1, 'Aus', '', -1];
        $Assoziation = $this->CreateProfilArray($Subtitles, $Assoziation);
        $this->RegisterProfileIntegerEx('Subtitels.' . $this->InstanceID . '.Kodi', '', '', '', $Assoziation);
    }

    /**
     * Erzeugt aus einem Objekt von Audiostreams ein IPS-Variablenprofil.
     */
    private function CreateAudioProfil(array $AudioStream): void
    {
        $Assoziation = $this->CreateProfilArray($AudioStream);
        $this->RegisterProfileIntegerEx('AudioStream.' . $this->InstanceID . '.Kodi', '', '', '', $Assoziation);
    }

    /**
     * Liefert den Namen des Profils für die Videospuren dieser Instanz.
     *
     * @access private
     * @return string Profilname
     */
    private function GetVideoStreamProfile(): string
    {
        return 'Kodi.' . $this->InstanceID . '.VideoStream';
    }

    /**
     * Legt die optionalen Statusvariablen des Video-Players an oder entfernt sie (Videospur, Audio-Verzögerung, Bildformat).
     *
     * @access private
     */
    private function RegisterVideoVariables(): void
    {
        if ($this->ReadPropertyBoolean(self::PropertyShowVideoStream)) {
            if (!IPS_VariableProfileExists($this->GetVideoStreamProfile())) {
                $this->RegisterProfileIntegerEx($this->GetVideoStreamProfile(), '', '', '', [[0, '1', '', -1]]);
            }
            $this->RegisterVariableInteger('videostream', $this->Translate('Videostream'), $this->GetVideoStreamProfile(), 31);
        } else {
            $this->UnregisterVariable('videostream');
            $this->UnregisterProfile($this->GetVideoStreamProfile());
        }
        if ($this->ReadPropertyBoolean(self::PropertyShowAudioDelay)) {
            // Kodi: Vielfache von 0,025 s, Standardbereich +/-10 s (Schema Player.SetAudioDelay)
            $this->RegisterProfileFloat(self::ProfileAudioDelay, 'Clock', '', ' s', -10, 10, 0.025, 3);
            $this->RegisterVariableFloat('audiodelay', $this->Translate('Audio delay'), self::ProfileAudioDelay, 32);
            $this->EnableAction('audiodelay');
        } else {
            $this->UnregisterVariable('audiodelay');
        }
        if ($this->ReadPropertyBoolean(self::PropertyShowViewMode)) {
            $Associations = [];
            foreach (self::ViewModes as $Index => $Mode) {
                $Associations[] = [$Index, $this->Translate('ViewMode.' . $Mode), '', -1];
            }
            $this->RegisterProfileIntegerEx(self::ProfileViewMode, 'Image', '', '', $Associations);
            $this->RegisterVariableInteger('viewmode', $this->Translate('View mode'), self::ProfileViewMode, 33);
            $this->EnableAction('viewmode');
        } else {
            $this->UnregisterVariable('viewmode');
        }
    }

    /**
     * Entfernt die optionalen Statusvariablen des Video-Players.
     *
     * @access private
     */
    private function UnregisterVideoVariables(): void
    {
        $this->UnregisterVariable('videostream');
        $this->UnregisterVariable('audiodelay');
        $this->UnregisterVariable('viewmode');
        $this->UnregisterProfile($this->GetVideoStreamProfile());
    }

    /**
     * Liest Audio-Verzögerung und Bildformat, sofern die Variablen existieren.
     * Optionale Abfrage, Fehler nur im Debug.
     *
     * @access private
     */
    private function RequestVideoSettings(): void
    {
        if ($this->PlayerId != self::Video) {
            return;
        }
        if (@$this->GetIDForIdent('audiodelay') !== false) {
            if (@$this->GetAudioDelay() === false) {
                $this->SendDebug('RequestVideoSettings', 'GetAudioDelay failed', 0);
            }
        }
        if (@$this->GetIDForIdent('viewmode') !== false) {
            if (@$this->GetViewMode() === false) {
                $this->SendDebug('RequestVideoSettings', 'GetViewMode failed', 0);
            }
        }
    }

    /**
     * Prüft, ob die Instanz ein aktiver Video-Player ist.
     *
     * @access private
     * @return bool true wenn Video-Player und aktiv, sonst false (mit Meldung).
     */
    /**
     * Sendet einen Befehl an den Bilder-Player.
     *
     * @param string $Method RPC-Methode im Namespace Player.
     * @param array $Params Parameter ohne playerid.
     * @return bool True bei Erfolg, sonst false.
     */
    private function SendPictureCommand(string $Method, array $Params): bool
    {
        if ($this->PlayerId != self::Picture) {
            trigger_error($this->Translate('Only available for the picture player.'), E_USER_NOTICE);
            return false;
        }
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        $KodiData = new Kodi_RPC_Data(self::$Namespace, $Method, array_merge(['playerid' => $this->PlayerId], $Params));
        $ret = $this->Send($KodiData);
        if (is_null($ret)) {
            return false;
        }
        if ($ret === 'OK') {
            return true;
        }
        trigger_error($this->Translate('Error on send ') . $Method . '.', E_USER_NOTICE);
        return false;
    }

    private function CheckVideoPlayer(): bool
    {
        if ($this->PlayerId != self::Video) {
            trigger_error($this->Translate('Only available for the video player.'), E_USER_NOTICE);
            return false;
        }
        if (!$this->isActive) {
            trigger_error($this->Translate('Player not active'), E_USER_NOTICE);
            return false;
        }
        return true;
    }

    /**
     * Erzeugt aus einem Array von Videospuren ein IPS-Variablenprofil.
     *
     * @access private
     */
    private function CreateVideoProfil(array $VideoStreams): void
    {
        if (@$this->GetIDForIdent('videostream') === false) {
            return;
        }
        $Associations = [];
        foreach ($VideoStreams as $Stream) {
            $Name = trim(($Stream->language ?? '') . ' ' . ($Stream->name ?? ''));
            if ($Name == '') {
                $Name = (string) ($Stream->index + 1);
            }
            if (($Stream->width ?? 0) > 0) {
                $Name .= ' (' . $Stream->width . 'x' . $Stream->height . ')';
            }
            $Associations[] = [$Stream->index, $Name, '', -1];
        }
        if (count($Associations) == 0) {
            $Associations[] = [0, '1', '', -1];
        }
        $this->RegisterProfileIntegerEx($this->GetVideoStreamProfile(), '', '', '', $Associations);
    }
}
