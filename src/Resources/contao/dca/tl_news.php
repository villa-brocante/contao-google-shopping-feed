<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('gsf_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('use_in_gsf', 'gsf_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('gsf_gtin', 'gsf_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('gsf_mpn', 'gsf_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

//Beschreibung des Feldes
$GLOBALS['TL_DCA']['tl_news']['fields']['use_in_gsf'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['activate_for_gsf'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => ['tl_class' => 'w100'],
    'sql'       => "char(1) NOT NULL default ''"
);

// GTIN
$GLOBALS['TL_DCA']['tl_news']['fields']['gsf_gtin'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['gsf_gtin'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(15) default ''"
);

// MPN
$GLOBALS['TL_DCA']['tl_news']['fields']['gsf_mpn'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_news']['gsf_mpn'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(15) default ''"
);
