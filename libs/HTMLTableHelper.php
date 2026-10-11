<?php

declare(strict_types=1);

namespace KodiBase;

/**
 * @project       KodiBase
 * @file          HTMLTableHelper.php
 * @package       Kodi
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       3.10
 */

/**
 * Namen (Suffix) der Eigenschaften für das Design einer HTML-Tabelle.
 * Module mit mehreren Tabellen stellen einen Präfix voran (z.B. 'TV' . TableProperty::Table).
 */
class TableProperty
{
    public const Table = 'Table';
    public const Columns = 'Columns';
    public const Rows = 'Rows';
    public const Buttons = 'Buttons';
}

/**
 * Zeilentypen einer HTML-Tabelle.
 */
class TableRow
{
    public const Even = 'even';
    public const Odd = 'odd';
    public const Active = 'active';
}

/**
 * Liest und konvertiert die alten Konfigurations-Skripte (bis Version 3.02) der HTML-Tabellen.
 * Ohne Symcon-Abhängigkeit, damit per Unit-Test prüfbar.
 */
class HTMLTableLegacy
{
    /** Marke, an der der Konfigurationsteil der alten Skripte endet. */
    public const ConfigEnd = '### Konfig ENDE';

    /** Zuordnung der neuen Tags zu den alten Style-Schlüsseln. */
    public const TableStyleMap = [
        '<table>'     => 'T',
        '<thead>'     => 'H',
        '<thead><tr>' => 'HR',
        '<tbody>'     => 'B'
    ];

    /** Zuordnung der Zeilentypen zu den alten Style-Schlüsseln (G = gerade, U = ungerade, A = aktiv). */
    public const RowStyleMap = [
        TableRow::Even   => 'G',
        TableRow::Odd    => 'U',
        TableRow::Active => 'A'
    ];

    /**
     * Führt den Konfigurationsteil eines alten Skripts aus und liefert das Array $Config.
     * Ausgeführt wird nur der Teil bis zur Marke '### Konfig ENDE', ohne Script-Engine.
     * Damit funktioniert es auch beim Start von Symcon und bei Skripten mit kurzem Open-Tag '<?'.
     *
     * @param string $Content Inhalt des alten Skripts.
     * @return array|false Das Array $Config oder false, wenn es nicht gelesen werden konnte.
     */
    public static function ExtractConfig(string $Content): array|false
    {
        $End = strpos($Content, self::ConfigEnd);
        if ($End === false) {
            return false;
        }
        $Start = 0;
        if (preg_match('/^\s*<\?(php)?/i', $Content, $Match)) {
            $Start = strlen($Match[0]);
        }
        $Code = substr($Content, $Start, $End - $Start);
        ob_start();
        try {
            $Config = (static function (string $Code): mixed
            {
                // Die alten Skripte brechen ab, wenn SENDER nicht 'Kodi' ist.
                $_IPS = ['SENDER' => 'Kodi'];
                $Config = [];
                @eval($Code);
                return $Config;
            })($Code);
        } catch (\Throwable $e) {
            return false;
        } finally {
            ob_end_clean();
        }
        if (!is_array($Config) || !isset($Config['Spalten']) || !is_array($Config['Spalten'])) {
            return false;
        }
        return $Config;
    }

    /**
     * Überträgt eine alte Konfiguration auf das neue Format der Eigenschaften.
     * Sichtbar sind danach genau die Spalten aus 'Spalten' in deren Reihenfolge, alle anderen werden ausgeblendet.
     * Spalten, die das Modul nicht kennt, werden übernommen.
     *
     * @param array $Legacy Das Array $Config aus dem alten Skript.
     * @param array $Default Die Standardkonfiguration des Moduls (Table, Columns, Rows, Buttons).
     * @return array Neue Konfiguration (Table, Columns, Rows, Buttons).
     */
    public static function Convert(array $Legacy, array $Default): array
    {
        $Style = (isset($Legacy['Style']) && is_array($Legacy['Style'])) ? $Legacy['Style'] : [];
        $Width = (isset($Legacy['Breite']) && is_array($Legacy['Breite'])) ? $Legacy['Breite'] : [];
        $Visible = array_map('strval', array_keys($Legacy['Spalten']));

        $Table = $Default['Table'];
        foreach ($Table as &$Tag) {
            $OldKey = self::TableStyleMap[$Tag['tag']] ?? null;
            if (($OldKey !== null) && isset($Style[$OldKey])) {
                $Tag['style'] = trim((string) $Style[$OldKey]);
            }
        }
        unset($Tag);

        $Columns = $Default['Columns'];
        $Known = array_column($Columns, 'key');
        foreach ($Visible as $Key) {
            if (!in_array($Key, $Known, true)) {
                $Columns[] = [
                    'index' => 0,
                    'key'   => $Key,
                    'name'  => '',
                    'show'  => true,
                    'width' => 100,
                    'color' => 0xffffff,
                    'align' => 'center',
                    'style' => ''
                ];
            }
        }
        $HiddenIndex = count($Visible);
        foreach ($Columns as &$Column) {
            $Key = $Column['key'];
            $Position = array_search($Key, $Visible, true);
            if ($Position !== false) {
                $Column['index'] = $Position;
                $Column['show'] = true;
                $Column['name'] = (string) $Legacy['Spalten'][$Key];
            } else {
                $Column['index'] = $HiddenIndex++;
                $Column['show'] = false;
            }
            if (isset($Width[$Key])) {
                $Column['width'] = (int) $Width[$Key];
            }
            $Extra = [];
            // <th>-Style: Farbe übernehmen. 'width' steckt schon in 'Breite', 'align' ist kein gültiges CSS.
            foreach (self::SplitCss((string) ($Style['HF' . $Key] ?? '')) as [$Name, $Value, $Raw]) {
                switch ($Name) {
                    case 'color':
                        $Color = self::CssColorToInt($Value);
                        if ($Color === false) {
                            $Extra[] = $Raw;
                        } else {
                            $Column['color'] = $Color;
                        }
                        break;
                    case 'width':
                    case 'align':
                        break;
                    case 'text-align':
                        $Column['align'] = $Value;
                        break;
                    default:
                        $Extra[] = $Raw;
                        break;
                }
            }
            // <td>-Style der geraden Zeile; die alten Skripte nutzen für alle Zeilen dieselben Werte.
            foreach (self::SplitCss((string) ($Style['DFG' . $Key] ?? '')) as [$Name, $Value, $Raw]) {
                if ($Name == 'text-align') {
                    $Column['align'] = $Value;
                } else {
                    $Extra[] = $Raw;
                }
            }
            $Column['style'] = implode(';', array_unique($Extra));
        }
        unset($Column);

        $Rows = $Default['Rows'];
        foreach ($Rows as &$Row) {
            $OldKey = 'BR' . (self::RowStyleMap[$Row['row']] ?? '');
            if (!isset($Style[$OldKey])) {
                continue;
            }
            $Extra = [];
            foreach (self::SplitCss((string) $Style[$OldKey]) as [$Name, $Value, $Raw]) {
                $Color = self::CssColorToInt($Value);
                if (($Name == 'background-color') && ($Color !== false)) {
                    $Row['bgcolor'] = $Color;
                } elseif (($Name == 'color') && ($Color !== false)) {
                    $Row['color'] = $Color;
                } else {
                    $Extra[] = $Raw;
                }
            }
            $Row['style'] = implode(';', $Extra);
        }
        unset($Row);

        $Buttons = $Default['Buttons'];
        if (isset($Legacy['Button']) && is_array($Legacy['Button'])) {
            $Buttons = [];
            foreach ($Legacy['Button'] as $Class => $Css) {
                $Buttons[] = ['class' => (string) $Class, 'style' => trim((string) $Css)];
            }
        }
        return ['Table' => $Table, 'Columns' => $Columns, 'Rows' => $Rows, 'Buttons' => $Buttons];
    }

    /**
     * Zerlegt einen CSS-Style in seine Deklarationen.
     *
     * @param string $Css Inhalt eines style-Attributes.
     * @return array Liste aus [Name (klein), Wert, Deklaration].
     */
    public static function SplitCss(string $Css): array
    {
        $Result = [];
        foreach (explode(';', $Css) as $Declaration) {
            $Declaration = trim($Declaration);
            if ($Declaration === '') {
                continue;
            }
            $Pair = explode(':', $Declaration, 2);
            $Result[] = [strtolower(trim($Pair[0])), trim($Pair[1] ?? ''), $Declaration];
        }
        return $Result;
    }

    /**
     * Wandelt eine CSS-Farbe im Format #rrggbb oder #rgb in einen Integer.
     *
     * @param string $Color CSS-Farbe.
     * @return int|false Farbe als Integer oder false bei anderen Formaten (rgb(), Farbnamen).
     */
    public static function CssColorToInt(string $Color): int|false
    {
        $Color = trim($Color);
        if (preg_match('/^#([0-9a-f]{6})$/i', $Color, $Match)) {
            return (int) hexdec($Match[1]);
        }
        if (preg_match('/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i', $Color, $Match)) {
            return (int) hexdec($Match[1] . $Match[1] . $Match[2] . $Match[2] . $Match[3] . $Match[3]);
        }
        return false;
    }
}

/**
 * Erzeugt HTML-Tabellen für ~HTMLBox-Variablen, deren Design in Eigenschaften der Instanz liegt.
 * Ersetzt die Konfigurations-Skripte bis Version 3.02.
 *
 * @method bool SendDebug(string $Message, mixed $Data, int $Format)
 */
trait HTMLTable
{
    /**
     * Erzeugt die Standardkonfiguration einer Tabelle.
     *
     * @param array $ColumnDefinitions Liste aus [Schlüssel, Überschrift (englisch), Breite in em, sichtbar].
     * @param bool $WithActiveRow true, wenn die Tabelle eine aktive Zeile kennt (Playlist).
     * @param array $Buttons Liste aus ['class' => CSS-Klasse, 'style' => CSS].
     * @return array Konfiguration (Table, Columns, Rows, Buttons).
     */
    protected function GetDefaultTableConfig(array $ColumnDefinitions, bool $WithActiveRow = false, array $Buttons = []): array
    {
        $Table = [
            ['tag' => '<table>', 'style' => 'margin:0 auto; font-size:0.8em;'],
            ['tag' => '<thead>', 'style' => ''],
            ['tag' => '<thead><tr>', 'style' => ''],
            ['tag' => '<tbody>', 'style' => '']
        ];
        $Columns = [];
        foreach ($ColumnDefinitions as $Index => [$Key, $Name, $Width, $Show]) {
            $Columns[] = [
                'index' => $Index,
                'key'   => $Key,
                'name'  => ($Name === '') ? '' : $this->Translate($Name),
                'show'  => $Show,
                'width' => $Width,
                'color' => 0xffffff,
                'align' => 'center',
                'style' => ''
            ];
        }
        $Rows = [
            ['row' => TableRow::Even, 'name' => $this->Translate('even'), 'bgcolor' => 0x000000, 'color' => 0xffffff, 'style' => ''],
            ['row' => TableRow::Odd, 'name' => $this->Translate('odd'), 'bgcolor' => 0x080808, 'color' => 0xffffff, 'style' => '']
        ];
        if ($WithActiveRow) {
            $Rows[] = ['row' => TableRow::Active, 'name' => $this->Translate('active'), 'bgcolor' => 0x808000, 'color' => 0xffffff, 'style' => ''];
        }
        return ['Table' => $Table, 'Columns' => $Columns, 'Rows' => $Rows, 'Buttons' => $Buttons];
    }

    /**
     * Registriert die Eigenschaften für das Design einer Tabelle.
     *
     * @param string $Prefix Präfix der Eigenschaften (leer bei nur einer Tabelle).
     * @param array $Default Standardkonfiguration aus GetDefaultTableConfig.
     */
    protected function RegisterTableProperties(string $Prefix, array $Default): void
    {
        $this->RegisterPropertyString($Prefix . TableProperty::Table, json_encode($Default['Table']));
        $this->RegisterPropertyString($Prefix . TableProperty::Columns, json_encode($Default['Columns']));
        $this->RegisterPropertyString($Prefix . TableProperty::Rows, json_encode($Default['Rows']));
        if (count($Default['Buttons']) > 0) {
            $this->RegisterPropertyString($Prefix . TableProperty::Buttons, json_encode($Default['Buttons']));
        }
    }

    /**
     * Überträgt das Design aus einem alten Konfigurations-Skript in die Eigenschaften (Aufruf aus Migrate).
     * Das Skript selbst bleibt unverändert erhalten, es wird nur nicht mehr benutzt.
     * Die alte Eigenschaft ist nicht mehr registriert und wird aus der Konfiguration entfernt.
     * Es gibt daher genau einen Versuch: Ist das Skript nicht lesbar, gilt das Standard-Design.
     *
     * @param object $Configuration Die Konfiguration aus den Daten von Migrate.
     * @param string $LegacyProperty Name der alten Eigenschaft mit der Skript-ID.
     * @param string $Prefix Präfix der neuen Eigenschaften.
     * @param array $Default Standardkonfiguration aus GetDefaultTableConfig.
     * @return bool true, wenn die Konfiguration verändert wurde.
     */
    protected function MigrateTableScript(object $Configuration, string $LegacyProperty, string $Prefix, array $Default): bool
    {
        if (!property_exists($Configuration, $LegacyProperty)) {
            return false;
        }
        $ScriptID = (int) $Configuration->{$LegacyProperty};
        unset($Configuration->{$LegacyProperty});
        if (($ScriptID <= 0) || !@IPS_ScriptExists($ScriptID)) {
            return true;
        }
        $Content = @IPS_GetScriptContent($ScriptID);
        $Legacy = is_string($Content) ? HTMLTableLegacy::ExtractConfig($Content) : false;
        if ($Legacy === false) {
            $this->LogMessage(sprintf($this->Translate('Could not read design of HTML table from script %d. Default design is used.'), $ScriptID), KL_WARNING);
            return true;
        }
        $New = HTMLTableLegacy::Convert($Legacy, $Default);
        $Configuration->{$Prefix . TableProperty::Table} = json_encode($New['Table']);
        $Configuration->{$Prefix . TableProperty::Columns} = json_encode($New['Columns']);
        $Configuration->{$Prefix . TableProperty::Rows} = json_encode($New['Rows']);
        if (count($Default['Buttons']) > 0) {
            $Configuration->{$Prefix . TableProperty::Buttons} = json_encode($New['Buttons']);
        }
        $this->SendDebug('Migrate ' . $Prefix . 'Table', $New, 0);
        $this->LogMessage(sprintf($this->Translate('Design of HTML table migrated from script %d. The script is no longer used and can be deleted.'), $ScriptID), KL_MESSAGE);
        return true;
    }

    /**
     * Liest das Design einer Tabelle aus den Eigenschaften.
     *
     * @param string $Prefix Präfix der Eigenschaften.
     * @param bool $WithButtons true, wenn die Tabelle Buttons mit eigenen CSS-Klassen nutzt.
     * @return array Table (tag => style), Columns (nur sichtbare, sortiert, key => Spalte), Rows (row => Zeile), Buttons.
     */
    protected function GetTableConfig(string $Prefix = '', bool $WithButtons = false): array
    {
        $Table = array_column(json_decode($this->ReadPropertyString($Prefix . TableProperty::Table), true), 'style', 'tag');
        $Columns = array_filter(json_decode($this->ReadPropertyString($Prefix . TableProperty::Columns), true), function (array $Column): bool
        {
            return $Column['show'] === true;
        });
        usort($Columns, function (array $a, array $b): int
        {
            return $a['index'] <=> $b['index'];
        });
        $Rows = array_column(json_decode($this->ReadPropertyString($Prefix . TableProperty::Rows), true), null, 'row');
        $Buttons = $WithButtons ? json_decode($this->ReadPropertyString($Prefix . TableProperty::Buttons), true) : [];
        return [
            'Table'   => $Table,
            'Columns' => array_column($Columns, null, 'key'),
            'Rows'    => $Rows,
            'Buttons' => $Buttons
        ];
    }

    /**
     * Liefert den Header der HTML-Tabelle inkl. JS-Rückkanal.
     *
     * @param array $Config Design aus GetTableConfig.
     * @return string HTML-String
     */
    protected function GetTableHeader(array $Config): string
    {
        $html = '';
        // JS Rückkanal erzeugen
        $html .= '<script>
//window.xhrGet' . $this->InstanceID . '=
    function xhrGet' . $this->InstanceID . '(o)
{
    var HTTP = new XMLHttpRequest();
    HTTP.open(\'GET\',o.url,true);
    HTTP.send();
    HTTP.addEventListener(\'load\', function(event)
    {
        if (HTTP.status >= 200 && HTTP.status < 300)
        {
            if (HTTP.responseText != \'OK\')
                sendError' . $this->InstanceID . '(HTTP.responseText);
        } else {
            sendError' . $this->InstanceID . '(HTTP.statusText);
        }
    });
};

function sendError' . $this->InstanceID . '(data)
{
var notify = document.getElementsByClassName("ipsNotifications")[0];
var newDiv = document.createElement("div");
newDiv.innerHTML =\'<div style="height:auto; visibility: hidden; overflow: hidden; transition: height 500ms ease-in 0s" class="ipsNotification"><div class="spacer"></div><div class="message icon error" onclick="document.getElementsByClassName(\\\'ipsNotifications\\\')[0].removeChild(this.parentNode);"><div class="ipsIconClose"></div><div class="content"><div class="title">' . $this->Translate('Error') . '</div><div class="text">\' + data + \'</div></div></div></div>\';
if (notify.childElementCount == 0)
	var thisDiv = notify.appendChild(newDiv.firstChild);
else
	var thisDiv = notify.insertBefore(newDiv.firstChild,notify.childNodes[0]);
var newheight = window.getComputedStyle(thisDiv, null)["height"];
thisDiv.style.height = "0px";
thisDiv.style.visibility = "visible";
function sleep (time) {
  return new Promise((resolve) => setTimeout(resolve, time));
}
sleep(10).then(() => {
	thisDiv.style.height = newheight;
})
}
</script>';
        // Button Styles erzeugen
        if (count($Config['Buttons']) > 0) {
            $html .= '<style>' . PHP_EOL;
            foreach ($Config['Buttons'] as $Button) {
                $html .= '.' . $Button['class'] . ' {' . $Button['style'] . '}' . PHP_EOL;
            }
            $html .= '</style>' . PHP_EOL;
        }
        // Kopf der Tabelle erzeugen
        $html .= '<table' . $this->GetStyleAttribute([$Config['Table']['<table>'] ?? '']) . '>' . PHP_EOL;
        $html .= '<colgroup>' . PHP_EOL;
        foreach ($Config['Columns'] as $Column) {
            $html .= '<col width="' . $Column['width'] . 'em" />' . PHP_EOL;
        }
        $html .= '</colgroup>' . PHP_EOL;
        $html .= '<thead' . $this->GetStyleAttribute([$Config['Table']['<thead>'] ?? '']) . '>' . PHP_EOL;
        $html .= '<tr' . $this->GetStyleAttribute([$Config['Table']['<thead><tr>'] ?? '']) . '>';
        foreach ($Config['Columns'] as $Column) {
            $Style = [];
            if ($Column['color'] >= 0) {
                $Style[] = 'color:' . sprintf('#%06x', $Column['color']);
            }
            $Style[] = 'text-align:' . $Column['align'];
            $Style[] = $Column['style'];
            $html .= '<th' . $this->GetStyleAttribute($Style) . '>' . $Column['name'] . '</th>';
        }
        $html .= '</tr>' . PHP_EOL;
        $html .= '</thead>' . PHP_EOL;
        $html .= '<tbody' . $this->GetStyleAttribute([$Config['Table']['<tbody>'] ?? '']) . '>' . PHP_EOL;
        return $html;
    }

    /**
     * Liefert eine Zeile der HTML-Tabelle.
     * Fehlende Felder, -1 und (optional) 0 werden leer dargestellt, Arrays kommagetrennt.
     *
     * @param array $Config Design aus GetTableConfig.
     * @param string $Row Zeilentyp (TableRow::Even, ::Odd oder ::Active).
     * @param array $Line Die Daten der Zeile (Schlüssel = Spalte).
     * @param string $Attributes Zusätzliche Attribute für das tr-Tag (z.B. onclick).
     * @param bool $ZeroAsEmpty true, wenn 0 als leeres Feld dargestellt werden soll.
     * @return string HTML-String
     */
    protected function GetTableRow(array $Config, string $Row, array $Line, string $Attributes = '', bool $ZeroAsEmpty = false): string
    {
        $Style = [];
        if (isset($Config['Rows'][$Row])) {
            $RowConfig = $Config['Rows'][$Row];
            if ($RowConfig['bgcolor'] >= 0) {
                $Style[] = 'background-color:' . sprintf('#%06x', $RowConfig['bgcolor']);
            }
            if ($RowConfig['color'] >= 0) {
                $Style[] = 'color:' . sprintf('#%06x', $RowConfig['color']);
            }
            $Style[] = $RowConfig['style'];
        }
        $html = '<tr' . $this->GetStyleAttribute($Style) . ($Attributes === '' ? '' : ' ' . $Attributes) . '>';
        foreach ($Config['Columns'] as $Key => $Column) {
            $Value = $Line[$Key] ?? '';
            if (($Value === -1) || ($ZeroAsEmpty && ($Value === 0))) {
                $Value = '';
            }
            if (is_array($Value)) {
                $Value = implode(', ', $Value);
            }
            $html .= '<td' . $this->GetStyleAttribute(['text-align:' . $Column['align'], $Column['style']]) . '>' . (string) $Value . '</td>';
        }
        $html .= '</tr>' . PHP_EOL;
        return $html;
    }

    /**
     * Liefert den Footer der HTML-Tabelle.
     *
     * @return string HTML-String
     */
    protected function GetTableFooter(): string
    {
        $html = '</tbody>' . PHP_EOL;
        $html .= '</table>' . PHP_EOL;
        return $html;
    }

    /**
     * Füllt ein ExpansionPanel im Konfigurationsformular mit den Listen für das Design einer Tabelle.
     *
     * @param array $Elements Elemente des Formulars (wird rekursiv durchsucht).
     * @param string $PanelName Name des ExpansionPanel.
     * @param string $Prefix Präfix der Eigenschaften.
     * @param bool $Visible Sichtbarkeit des Panels.
     * @param bool $WithButtons true, wenn auch die Button-Styles angezeigt werden.
     * @return bool true, wenn das Panel gefunden wurde.
     */
    protected function FillTableFormPanel(array &$Elements, string $PanelName, string $Prefix, bool $Visible, bool $WithButtons = false): bool
    {
        foreach ($Elements as &$Element) {
            if (($Element['name'] ?? '') === $PanelName) {
                $Element['visible'] = $Visible;
                $Element['items'] = $this->GetTableFormElements($Prefix, $WithButtons);
                return true;
            }
            if (isset($Element['items']) && is_array($Element['items'])) {
                if ($this->FillTableFormPanel($Element['items'], $PanelName, $Prefix, $Visible, $WithButtons)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Liefert die Listen für das Design einer Tabelle im Konfigurationsformular.
     *
     * @param string $Prefix Präfix der Eigenschaften.
     * @param bool $WithButtons true, wenn auch die Button-Styles angezeigt werden.
     * @return array Formular-Elemente
     */
    protected function GetTableFormElements(string $Prefix, bool $WithButtons): array
    {
        $CssColumn = [
            'caption' => 'CSS',
            'name'    => 'style',
            'width'   => 'auto',
            'edit'    => [
                'caption' => 'CSS:',
                'type'    => 'ValidationTextBox'
            ]
        ];
        $ColorColumn = [
            'caption' => 'Color',
            'name'    => 'color',
            'width'   => '100px',
            'edit'    => [
                'caption'          => 'Color of text:',
                'type'             => 'SelectColor',
                'allowTransparent' => true
            ]
        ];
        $Elements = [
            [
                'type'     => 'List',
                'name'     => $Prefix . TableProperty::Table,
                'caption'  => 'Table design',
                'rowCount' => 4,
                'add'      => false,
                'delete'   => false,
                'columns'  => [
                    [
                        'name'    => 'tag',
                        'caption' => 'HTML-Tag',
                        'width'   => '120px',
                        'save'    => true
                    ],
                    $CssColumn
                ]
            ],
            [
                'type'     => 'List',
                'name'     => $Prefix . TableProperty::Columns,
                'caption'  => 'Table columns',
                'rowCount' => 10,
                'add'      => false,
                'delete'   => false,
                'sort'     => [
                    'column'    => 'index',
                    'direction' => 'ascending'
                ],
                'columns'  => [
                    [
                        'name'    => 'key',
                        'caption' => 'Data',
                        'width'   => '110px',
                        'save'    => true
                    ],
                    [
                        'caption' => 'Visible',
                        'name'    => 'show',
                        'width'   => '80px',
                        'edit'    => [
                            'caption' => 'Visible as column?',
                            'type'    => 'CheckBox'
                        ]
                    ],
                    [
                        'caption' => 'No.',
                        'name'    => 'index',
                        'width'   => '60px',
                        'edit'    => [
                            'caption' => 'Position of this column:',
                            'type'    => 'NumberSpinner'
                        ]
                    ],
                    [
                        'caption' => 'Display name',
                        'name'    => 'name',
                        'width'   => '150px',
                        'edit'    => [
                            'caption' => 'Display name:',
                            'type'    => 'ValidationTextBox'
                        ]
                    ],
                    [
                        'caption' => 'Width',
                        'name'    => 'width',
                        'width'   => '80px',
                        'edit'    => [
                            'caption' => 'Width of this column:',
                            'type'    => 'NumberSpinner',
                            'suffix'  => 'em'
                        ]
                    ],
                    [
                        'caption' => 'Alignment',
                        'name'    => 'align',
                        'width'   => '110px',
                        'edit'    => [
                            'type'    => 'Select',
                            'caption' => 'Text alignment:',
                            'options' => [
                                ['caption' => 'Left', 'value' => 'left'],
                                ['caption' => 'Center', 'value' => 'center'],
                                ['caption' => 'Right', 'value' => 'right']
                            ]
                        ]
                    ],
                    $ColorColumn,
                    $CssColumn
                ]
            ],
            [
                'type'     => 'List',
                'name'     => $Prefix . TableProperty::Rows,
                'caption'  => 'Table rows',
                'rowCount' => 3,
                'add'      => false,
                'delete'   => false,
                'columns'  => [
                    [
                        'name'    => 'row',
                        'caption' => '',
                        'width'   => '50px',
                        'save'    => true,
                        'visible' => false
                    ],
                    [
                        'name'    => 'name',
                        'caption' => 'Row',
                        'save'    => true,
                        'width'   => '150px'
                    ],
                    [
                        'caption' => 'Background color',
                        'name'    => 'bgcolor',
                        'width'   => '170px',
                        'edit'    => [
                            'caption'          => 'Color of background:',
                            'type'             => 'SelectColor',
                            'allowTransparent' => true
                        ]
                    ],
                    $ColorColumn,
                    $CssColumn
                ]
            ]
        ];
        if ($WithButtons) {
            $Elements[] = [
                'type'     => 'List',
                'name'     => $Prefix . TableProperty::Buttons,
                'caption'  => 'Button design',
                'rowCount' => 5,
                'add'      => false,
                'delete'   => false,
                'columns'  => [
                    [
                        'name'    => 'class',
                        'caption' => 'CSS class',
                        'width'   => '120px',
                        'save'    => true
                    ],
                    [
                        'caption' => 'CSS',
                        'name'    => 'style',
                        'width'   => 'auto',
                        'edit'    => [
                            'caption'   => 'CSS:',
                            'type'      => 'ValidationTextBox',
                            'multiline' => true
                        ]
                    ]
                ]
            ];
        }
        return $Elements;
    }

    /**
     * Baut ein style-Attribut aus mehreren CSS-Teilen.
     *
     * @param array $Parts CSS-Teile (leere werden ignoriert).
     * @return string Das Attribut inkl. führendem Leerzeichen oder ein leerer String.
     */
    private function GetStyleAttribute(array $Parts): string
    {
        $Css = implode(';', array_filter(array_map(function ($Part): string
        {
            return trim((string) $Part, " \t\n\r\0\x0B;");
        }, $Parts), 'strlen'));
        if ($Css === '') {
            return '';
        }
        return ' style="' . htmlspecialchars($Css . ';', ENT_QUOTES) . '"';
    }
}
